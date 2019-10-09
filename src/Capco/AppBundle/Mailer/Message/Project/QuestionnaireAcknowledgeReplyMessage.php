<?php

namespace Capco\AppBundle\Mailer\Message\Project;

use Capco\AppBundle\Entity\Reply;
use Capco\AppBundle\Mailer\Message\DefaultMessage;

final class QuestionnaireAcknowledgeReplyMessage extends DefaultMessage
{
    public static function create(
        string $recipientEmail,
        Reply $reply,
        string $projectTitle,
        \DateTimeInterface $replyUpdatedAt,
        string $siteName,
        string $state,
        string $userUrl,
        string $configUrl,
        string $baseUrl,
        string $stepUrl,
        string $questionnaireStepTitle
    ): self {
        return new self(
            $recipientEmail,
            null,
            "reply.notify.user.${state}",
            static::getMySubjectVars($questionnaireStepTitle),
            '@CapcoMail/acknowledgeReply.html.twig',
            static::getMyTemplateVars(
                $projectTitle,
                $replyUpdatedAt,
                $siteName,
                $reply,
                $state,
                $userUrl,
                $configUrl,
                $baseUrl,
                $stepUrl
            )
        );
    }

    private static function getMyTemplateVars(
        string $title,
        \DateTimeInterface $updatedAt,
        string $siteName,
        Reply $reply,
        string $state,
        string $userUrl,
        string $configUrl,
        string $baseUrl,
        string $stepUrl
    ): array {
        $now = new \DateTime();

        return [
            'projectTitle' => self::escape($title),
            'replyUpdatedAt' => $updatedAt,
            'siteName' => self::escape($siteName),
            'date' => $reply->getPublishedAt() ? $reply->getPublishedAt() : $now,
            'time' => $reply->getPublishedAt()
                ? $reply->getPublishedAt()->format('H:i:s')
                : $now->format('H:i:s'),
            'authorName' => $reply->getAuthor()->getUsername(),
            'questionnaireStepTitle' => $reply->getStep()->getTitle(),
            'questionnaireEndDate' => $reply->getStep()->getEndAt(),
            'state' => $state,
            'userUrl' => $userUrl,
            'configUrl' => $configUrl,
            'baseUrl' => $baseUrl,
            'stepUrl' => $stepUrl,
            'timeless' => $reply->getStep()->isTimeless()
        ];
    }

    private static function getMySubjectVars(string $questionnaireStepTitle): array
    {
        return [
            '{questionnaireStepTitle}' => $questionnaireStepTitle
        ];
    }
}
