<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
namespace Kuink\Core;

class Rank
{

    public const MIN_CHAR = 'a';
    public const MAX_CHAR = 'z';

    private $prev;
    private $next;

    /**
     * Rank constructor.
     */
    public function __construct(string $prev, string $next)
    {
        $this->setPrev($prev);
        $this->setNext($next);
    }

    private function setPrev(string $prev)
    {
        $this->prev = $prev === '' ? self::MIN_CHAR : $prev;
    }

    private function setNext(string $next)
    {
        $this->next = $next === '' ? self::MAX_CHAR : $next;
    }

    public function get()
    {
        $rank = '';
        $i = 0;

        while (true) {
            $prevChar = $this->getChar($this->prev, $i, self::MIN_CHAR);
            $nextChar = $this->getChar($this->next, $i, self::MAX_CHAR);

            if ($prevChar === $nextChar) {
                $rank .= $prevChar;
                $i++;
                continue;
            }

            $midChar = $this->mid($prevChar, $nextChar);
            if (in_array($midChar, [$prevChar, $nextChar])) {
                $rank .= $prevChar;
                $i++;
                continue;
            }

            $rank .= $midChar;
            break;
        }

        return $rank;
    }

    private function getChar(string $s, int $i, string $defaultChar)
    {
         return $s[$i] ?? $defaultChar;
    }

    private function mid(string $prev, string $next)
    {
        if (ord($prev) > ord($next)) {
            return ($prev);
        }

        return chr((ord($prev) + ord($next)) / 2);
    }


}

?>