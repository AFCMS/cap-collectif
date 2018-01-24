<?php

namespace Capco\AppBundle\Mailer\Message;

use Capco\AppBundle\Entity\Opinion;

final class NewOpinionModeratorMessage extends Message
{
    public static function create(Opinion $opinion, string $moderatorEmail, string $moderatorName, string $opinionLink, string $authorLink): self
    {
        return new self(
            $moderatorEmail,
            $moderatorName,
            'notification-subject-new-proposal',
            static::getTemplateVars(
                $opinion->getAuthor()->getUsername(),
                $opinion->getProject()->getTitle(),
            ),
            'notification-new-proposal',
            static::getTemplateVars(
                $opinion->getStep()->getProject()->getName(),
                $opinion->getAuthor()->getUsername(),
                $authorLink,
                $opinionLink
            )
        );
    }

    private static function getMyTemplateVars(
        string $projectName,
        string $authorName,
        string $authorLink,
        string $opinionLink
    ): array {
        return [
            'projectName' => self::escape($projectName),
            'authorName' => self::escape($authorName),
            'authorLink' => $authorLink,
            'opinionLink' => $opinionLink,
        ];
    }

    private static function getMySubjectVars(
        string $authorName,
        string $projectName,
    ): array {
        return [
            'projectName' => self::escape($projectName),
            'authorName' => self::escape($authorName),
        ];
    }
}
