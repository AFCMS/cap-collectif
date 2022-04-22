<?php
namespace Capco\AppBundle\Controller\Api;

use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\Form\Type\ApiAdminRegistrationFormType;
use Capco\UserBundle\Form\Type\ApiProfileAccountFormType;
use Capco\UserBundle\Form\Type\ApiProfileFormType;
use Capco\UserBundle\Form\Type\ApiRegistrationFormType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class UsersController extends FOSRestController
{
    /**
     * @Get("/users_counters")
     * @View()
     */
    public function getUsersCountersAction()
    {
        $registeredContributorCount = $this->get(
            'capco.user.repository'
        )->getRegisteredContributorCount();
        $anonymousComments = $this->get('capco.comment.repository')->getAnonymousCount();

        return [
            'contributors' => $registeredContributorCount + $anonymousComments + $anonymousVoters,
            'registeredContributors' => $registeredContributorCount,
            'anonymousComments' => $anonymousComments,
        ];
    }

    /**
     * @Post("/users/search")
     * @View(statusCode=200, serializerGroups={"UserId", "UsersInfos"})
     */
    public function getUsersSearchAction(Request $request)
    {
        $terms = $request->request->has('terms') ? $request->request->get('terms') : null;
        $notInIds = $request->request->has('notInIds') ? $request->request->get('notInIds') : null;

        return $this->get('capco.search.user_search')->searchAllUsers($terms, $notInIds);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get users",
     *  statusCodes={
     *    200 = "Returned when successful",
     *  }
     * )
     *
     * @Get("/users")
     * @QueryParam(name="type", requirements="[a-z]+", nullable=true)
     * @QueryParam(name="from", nullable=true)
     * @QueryParam(name="to", nullable=true)
     * @QueryParam(name="email", requirements=@Assert\Email, nullable=true)
     * @View(serializerGroups={"UserId"})
     */
    public function getUsersAction(ParamFetcherInterface $paramFetcher)
    {
        $type = $paramFetcher->get('type');
        $from = $paramFetcher->get('from');
        $to = $paramFetcher->get('to');
        $email = $paramFetcher->get('email');
        $userType = null;

        if ($type) {
            $userType = $this->container->get('capco.user_type.repository')->findOneBySlug($type);
            if (!$userType) {
                throw new BadRequestHttpException(
                    "This user type doesn't exist, please use a correct slug."
                );
            }
        }

        if ($email) {
            $users = $this->container->get('capco.user.repository')->findBy(['email' => $email]);
        } else {
            $users = $this->container->get('capco.user.repository')->getPublishedWith(
                $userType,
                $from,
                $to
            );
        }

        return ['count' => \count($users), 'users' => $users];
    }

    /**
     * Create a user.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Create a user.",
     *  statusCodes={
     *    201 = "Returned when successful",
     *  }
     * )
     *
     * @Post("/users", defaults={"_feature_flags" = "registration"})
     * @View(statusCode=201, serializerGroups={"UserId"})
     */
    public function postUserAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();

        $creatingAnAdmin = $this->getUser() && $this->getUser()->isAdmin();

        $formClass = $creatingAnAdmin
            ? ApiAdminRegistrationFormType::class
            : ApiRegistrationFormType::class;
        $form = $this->createForm($formClass, $user);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $userManager->updatePassword($user);

        // This allow the user to login
        $user->setEnabled(true);

        // We generate a confirmation token to validate email
        $token = $this->get('fos_user.util.token_generator')->generateToken();
        $user->setConfirmationToken($token);

        if ($creatingAnAdmin) {
            $this->get('capco.user_notifier')->adminConfirmation($user);
        } else {
            $this->get('capco.fos_notifier')->sendConfirmationEmailMessage($user);
        }

        $userManager->updateUser($user);

        return $user;
    }

    /**
     * @Put("/users/me")
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=204, serializerGroups={})
     */
    public function putMeAction(Request $request)
    {
        if ($request->request->has('phone')) {
            return $this->updatePhone($request);
        }
        if ($request->request->has('email')) {
            return $this->updateEmail($request);
        }
    }

    /**
     * @Post("/account/cancel_email_change")
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=200, serializerGroups={})
     */
    public function cancelEmailChangeAction()
    {
        $user = $this->getUser();
        $user->setNewEmailToConfirm(null);
        $user->setNewEmailConfirmationToken(null);
        $this->getDoctrine()
            ->getManager()
            ->flush();
    }

    /**
     * @Post("/account/resend_confirmation_email", defaults={"_feature_flags" = "registration"})
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=201, serializerGroups={})
     */
    public function postResendEmailConfirmationAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isEmailConfirmed() && !$user->getNewEmailToConfirm()) {
            throw new BadRequestHttpException('Already confirmed.');
        }

        // security against mass click email resend
        if ($user->getEmailConfirmationSentAt() > (new \DateTime())->modify('- 1 minutes')) {
            throw new BadRequestHttpException('Email already sent less than a minute ago.');
        }

        if ($user->getNewEmailToConfirm()) {
            $this->get('capco.user_notifier')->newEmailConfirmation($user);
        } else {
            $this->get('capco.fos_notifier')->sendConfirmationEmailMessage($user);
        }

        $user->setEmailConfirmationSentAt(new \DateTime());
        $this->getDoctrine()
            ->getManager()
            ->flush();
    }

    /**
     * @Post("/send-sms-confirmation", defaults={"_feature_flags" = "phone_confirmation"})
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=201, serializerGroups={})
     */
    public function postSendSmsConfirmationAction()
    {
        $user = $this->getUser();
        if ($user->isPhoneConfirmed()) {
            throw new BadRequestHttpException('Already confirmed.');
        }

        if (!$user->getPhone()) {
            throw new BadRequestHttpException('No phone.');
        }

        // security against mass click sms resend
        if (
            $user->getSmsConfirmationSentAt() &&
            $user->getSmsConfirmationSentAt() > (new \DateTime())->modify('- 3 minutes')
        ) {
            throw new BadRequestHttpException('sms_already_sent_recently');
        }

        try {
            $this->get('sms.service')->confirm($user);
        } catch (\Services_Twilio_RestException $e) {
            $this->get('logger')->error($e->getMessage());
            throw new BadRequestHttpException('sms_failed_to_send');
        }

        $user->setSmsConfirmationSentAt(new \DateTime());
        $this->getDoctrine()
            ->getManager()
            ->flush();
    }

    /**
     * @Post("/sms-confirmation", defaults={"_feature_flags" = "phone_confirmation"})
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=201, serializerGroups={})
     */
    public function postSmsConfirmationAction(Request $request)
    {
        $user = $this->getUser();
        if ($user->isPhoneConfirmed()) {
            throw new BadRequestHttpException('Already confirmed.');
        }

        if (!$user->getSmsConfirmationCode()) {
            throw new BadRequestHttpException('Ask a confirmation message before.');
        }

        if ($request->request->get('code') !== $user->getSmsConfirmationCode()) {
            throw new BadRequestHttpException('sms_code_invalid');
        }

        $user->setPhoneConfirmed(true);
        $user->setSmsConfirmationSentAt(null);
        $user->setSmsConfirmationCode(null);
        $this->getDoctrine()
            ->getManager()
            ->flush();
    }

    private function updatePhone(Request $request)
    {
        $user = $this->getUser();
        $previousPhone = $user->getPhone();

        $form = $this->createForm(ApiProfileFormType::class, $user);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        // If phone is updated we have to make sure it's sms confirmed again
        if (null !== $previousPhone && $previousPhone !== $user->getPhone()) {
            $user->setPhoneConfirmed(false);
            // TODO: security breach user can send unlimited sms if he change his number
            $user->setSmsConfirmationSentAt(null);
        }

        $this->getDoctrine()
            ->getManager()
            ->flush();
    }

    private function updateEmail(Request $request)
    {
        $user = $this->getUser();
        $newEmailToConfirm = $request->request->get('email');
        $password = $request->request->get('password');
        $toggleManager = $this->container->get(Manager::class);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            return new JsonResponse(
                ['message' => 'You must specify your password to update your email.'],
                400
            );
        }

        if ($this->container->get('capco.user.repository')->findOneByEmail($newEmailToConfirm)) {
            return new JsonResponse(['message' => 'Already used email.'], 400);
        }

        if (
            $toggleManager->isActive('restrict_registration_via_email_domain') &&
            !$this->container->get('capco.email_domain.repository')->findOneBy([
                'value' => explode('@', $newEmailToConfirm)[1],
            ])
        ) {
            return new JsonResponse(['message' => 'Unauthorized email domain.'], 400);
        }

        $form = $this->createForm(ApiProfileAccountFormType::class, $user);
        $form->submit(['newEmailToConfirm' => $newEmailToConfirm], false);

        if (!$form->isValid()) {
            return $form;
        }

        // We generate a confirmation token to validate the new email
        $token = $this->get('fos_user.util.token_generator')->generateToken();

        $user->setNewEmailConfirmationToken($token);
        $this->get('capco.user_notifier')->newEmailConfirmation($user);

        $this->getDoctrine()
            ->getManager()
            ->flush();
    }
}
