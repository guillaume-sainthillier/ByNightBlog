<?php

class NewsGenerator {

    const DATE_FONT    = 'Sanchez-Regular.ttf';
    const HEADING_FONT = 'Arimo-Regular.ttf';
    const TEXT_FONT    = 'Arimo-Regular.ttf';
    const EDITION_FONT = 'Sanchez-Regular.ttf';

    const DATE_COLOR    = ['r' => 128, 'g' => 128, 'b' => 128];
    const HEADING_COLOR = ['r' => 34, 'g' => 34, 'b' => 34];
    const TEXT_COLOR    = ['r' => 34, 'g' => 34, 'b' => 34];
    const EDITION_COLOR = ['r' => 255, 'g' => 255, 'b' => 255];

    const DATE_SIZE    = 14;
    const HEADING_SIZE = 16;
    const TEXT_SIZE    = 12;
    const EDITION_SIZE = 40;

    const TEXT_X          = ['left' => 10, 'right' => 1170];
    const TEXT_Y          = 390 + self::HEADING_SIZE;
    const TEXT_MAX_WIDTH  = 320;
    const TEXT_MAX_HEIGHT = 500;

    private $image;
    private $currentTextOffset;

    public function __construct($filename) {
        $this->image             = imagecreatefromjpeg($filename);
        $this->currentTextOffset = [
            'x' => self::TEXT_X['left'],
            'y' => self::TEXT_Y,
        ];
    }

    public function addSection($name, array $events) {
        $nbLines           = 0;
        $lines             = [];
        $textLineHeight    = 1.5;
        $headingLineHeight = 1.25;

        foreach ($events as $event) {
            $eventLines             = $this->wrapTextWithOverflow($event->getNom());
            $lines[$event->getId()] = $eventLines;
            $nbLines += count($lines);
        }

        if (!$this->isBounded($nbLines, $textLineHeight, $headingLineHeight)) {
            if ($this->currentTextOffset['x'] !== self::TEXT_X['right']) {
                $this->currentTextOffset['x'] = self::TEXT_X['right'];
                $this->currentTextOffset['y'] = self::TEXT_Y;
            } else {
                return;
            }
        }

        $this->addText($name, $this->currentTextOffset['x'], $this->currentTextOffset['y'], self::HEADING_SIZE, self::HEADING_COLOR, self::HEADING_FONT);
        $this->currentTextOffset['y'] += $headingLineHeight * self::HEADING_SIZE;

        foreach ($lines as $texts) {
            foreach ($texts as $text) {
                $this->addText($text, $this->currentTextOffset['x'], $this->currentTextOffset['y'], self::TEXT_SIZE, self::TEXT_COLOR, self::TEXT_FONT);
                $this->currentTextOffset['y'] += $textLineHeight * self::TEXT_SIZE;
            }
            $this->currentTextOffset['y'] += 2;
        }

        $this->currentTextOffset['y'] += 10;
    }

    public function isBounded($nbLines, $textLineHeight, $headingLineHeight) {
        $linesHeight = 0.75 * ($nbLines * $textLineHeight * self::TEXT_SIZE + ($headingLineHeight * self::HEADING_SIZE));
        return $this->currentTextOffset['y'] + $linesHeight <= self::TEXT_Y + self::TEXT_MAX_HEIGHT;
    }

    /**
     * Splits overflowing text into array of strings.
     * @param string $text
     * @return string[]
     */
    protected function wrapTextWithOverflow($text) {
        $lines = array();
        // Split text explicitly into lines by \n, \r\n and \r
        $explicitLines = preg_split('/\n|\r\n?/', $text);
        foreach ($explicitLines as $line) {
            // Check every line if it needs to be wrapped
            $words = explode(" ", $line);
            $line  = $words[0];
            for ($i = 1; $i < count($words); $i++) {
                $box = $this->calculateBox($line . " " . $words[$i]);
                if (($box[4] - $box[6]) >= self::TEXT_MAX_WIDTH) {
                    $lines[] = $line;
                    $line    = $words[$i];
                } else {
                    $line .= " " . $words[$i];
                }
            }
            $lines[] = $line;
        }
        return $lines;
    }

    protected function calculateBox($text) {
        return imageftbbox(self::TEXT_SIZE, 0, $this->getFont(self::TEXT_FONT), $text);
    }

    public function setDate(\DateTime $from, \DateTime $to) {
        $formatter  = IntlDateFormatter::create(null, IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $offsetY    = 100;
        $offsetX    = 70;
        $lineHeight = 22;

        $this->addText('Semaine', $offsetX + 30, $offsetY, self::DATE_SIZE, self::DATE_COLOR, self::DATE_FONT);
        $offsetY += $lineHeight;

        $this->addText('du ' . $formatter->format($from->getTimestamp()), $offsetX, $offsetY, self::DATE_SIZE, self::DATE_COLOR, self::DATE_FONT);
        $offsetY += $lineHeight;

        $this->addText('au ' . $formatter->format($to->getTimestamp()), $offsetX, $offsetY, self::DATE_SIZE, self::DATE_COLOR, self::DATE_FONT);
    }

    public function setNumeroEdition($numeroEdition) {
        $offsetY = 145;
        $offsetX = 1333 - ((self::EDITION_SIZE / 3) * (strlen($numeroEdition) - 1));
        $this->addText($numeroEdition, $offsetX, $offsetY, self::EDITION_SIZE, self::EDITION_COLOR, self::EDITION_FONT);
    }

    private function addText($text, $x, $y, $size, array $colors, $font) {
        imagettftext($this->image, $size, 0, $x, $y, $this->getColor($colors), $this->getFont($font), $text);
    }

    private function getFont($fontName) {
        return FONT_PATH . DIRECTORY_SEPARATOR . $fontName;
    }

    private function getColor(array $colors) {
        return imagecolorallocate($this->image, $colors['r'], $colors['g'], $colors['b']);
    }

    public function render() {
        ob_start();

        // Send Image to Browser
        imagejpeg($this->image, NULL, 100);

        // Clear Memory
        imagedestroy($this->image);

        $content = ob_get_contents();
        ob_clean();

        return $content;
    }
}