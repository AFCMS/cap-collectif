<?php

namespace Capco\AppBundle\Traits;

use Doctrine\ORM\Mapping as ORM;

trait TextableTrait
{
    /**
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    public function getBody()
    {
        return $this->body;
    }

    public function getBodyText()
    {
        return strip_tags($this->body);
    }

    public function setBody(string $body = null): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBodyExcerpt(int $nb = 100): string
    {
        $excerpt = substr($this->body, 0, $nb);
        $excerpt .= '…';

        return $excerpt;
    }

    public function getBodyTextExcerpt(int $nb = 100): string
    {
        $text = strip_tags($this->body);

        if (strlen($text) > $nb) {
            $text = substr($text, 0, $nb);
            $text .= '[…]';
        }

        return $text;
    }
}
