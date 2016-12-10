<?php

namespace IjorTengab\Tools\Abstracts;

/**
 * Abstract untuk menganalisis karakter satu per satu. Masukkan string pada
 * pada property $raw. Jalankan method looping() maka property akan terisi
 * sesuai dengan kondisi karakter saat ini.
 *
 * Secara default, karakter break dengan value "\r\n" akan dimanipulasi sehingga
 * di analyze sebagai satu karakter. @see method manipulateCurrentCharacter().
 *
 * Contoh:
 *
 * Kita buat file sebagai berikut:
 *
 * <?php
 * require __DIR__ . '/src/Abstracts/AbstractAnalyzeCharacter.php';
 * require __DIR__ . '/functions/var_dump.php';
 * use IjorTengab\Tools\Abstracts\AbstractAnalyzeCharacter;
 * use function IjorTengab\Override\PHP\VarDump\var_dump;
 * use const IjorTengab\Override\PHP\VarDump\SUBJECT;
 *
 * class AnalyzeCharacter extends AbstractAnalyzeCharacter {
 *     protected function analyzeCurrentLine()
 *     {
 *         var_dump($this->current_line, SUBJECT);
 *         var_dump($this->current_line_string, SUBJECT);
 *     }
 *     protected function analyzeCurrentCharacter()
 *     {
 *         var_dump($this->current_character, SUBJECT);
 *         var_dump($this->current_character_string, SUBJECT);
 *     }
 * }
 * $content = <<<CONTENT
 * a~z
 * 0~9
 * CONTENT;
 * $analyze = new AnalyzeCharacter($content);
 * $analyze->looping();
 * ?>
 *
 * Output dari Hasil eksekusi file diatas adalah:
 *
 * var_dump($this->current_line):
 * int(1)
 * var_dump($this->current_line_string):
 * string(3) "a~z"
 * var_dump($this->current_character):
 * int(0)
 * var_dump($this->current_character_string):
 * string(1) "a"
 * var_dump($this->current_character):
 * int(1)
 * var_dump($this->current_character_string):
 * string(1) "~"
 * var_dump($this->current_character):
 * int(2)
 * var_dump($this->current_character_string):
 * string(1) "z"
 * var_dump($this->current_character):
 * int(4)
 * var_dump($this->current_character_string):
 * string(2) "
 * "
 * var_dump($this->current_line):
 * int(2)
 * var_dump($this->current_line_string):
 * string(3) "0~9"
 * var_dump($this->current_character):
 * int(5)
 * var_dump($this->current_character_string):
 * string(1) "0"
 * var_dump($this->current_character):
 * int(6)
 * var_dump($this->current_character_string):
 * string(1) "~"
 * var_dump($this->current_character):
 * int(7)
 * var_dump($this->current_character_string):
 * string(1) "9"
 *
 */
abstract class AbstractAnalyzeCharacter
{
    protected $raw = '';
    protected $current_column = 1;
    protected $current_line = 1;
    protected $current_line_string = null;
    protected $current_character = 0;
    protected $current_character_string = null;
    protected $next_character_string = null;
    protected $prev_character_string = null;
    protected $is_last = false;
    protected $is_break = false;

    /**
     * Saat looping berada pada baris baru, maka method ini akan dijalankan.
     */
    abstract protected function analyzeCurrentLine();

    /**
     * Method ini akan dijalankan sebanyak karakter yang ada pada property $raw.
     * Untuk mengetahui karakter saat ini, lihat pada property
     * $current_character_string.
     */
    abstract protected function analyzeCurrentCharacter();

    /**
     * Construct.
     */
    public function __construct($raw = null)
    {
        if (is_string($raw)) {
            $this->raw = $raw;
        }
    }

    /**
     * Mulai melakukan analisis dengan cara
     * walk through each character.
     */
    public function looping()
    {
        if (!is_string($this->raw) || empty($this->raw)) {
            return;
        }
        if (false === $this->validate()) {
            return;
        }
        $this->beforeLooping();
        do {
            if ($this->current_column === 1) {
                // Current Line.
                $this->populateCurrentLine();
                $this->manipulateCurrentLine();
                $this->beforeAnalyzeCurrentLine();
                $this->analyzeCurrentLine();
                $this->afterAnalyzeCurrentLine();
            }
            // Current Character.
            $this->populateCurrentCharacter();
            $this->manipulateCurrentCharacter();
            $this->assignCurrentCharacter();
            $this->beforeAnalyzeCurrentCharacter();
            $this->analyzeCurrentCharacter();
            $this->afterAnalyzeCurrentCharacter();
            $this->prepareNextLoop();
            $this->resetAssignCharacter();
        } while($this->next_character_string !== false);
        $this->afterLooping();
    }

    /**
     * Berikan return === false, maka looping akan dibatalkan.
     * Selain ```false```, looping akan dijalankan meski return === null.
     */
    protected function validate()
    {
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function beforeLooping()
    {
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function afterLooping()
    {
    }

    /**
     * Method untuk mengisi property $current_line_string.
     */
    protected function populateCurrentLine()
    {
        $this->current_line_string = '';
        $leftover = substr($this->raw, $this->current_character);
        if (preg_match('/.*/', $leftover, $match)) {
            // $this->current_line_string berisi string tanpa break.
            $this->current_line_string = rtrim($match[0], "\r\n");
        }
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     *
     * Gunakan method ini, jika anda melakukan manipulasi isi dari property
     * $current_line_string.
     */
    protected function manipulateCurrentLine()
    {
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function beforeAnalyzeCurrentLine()
    {
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function afterAnalyzeCurrentLine()
    {
    }

    /**
     * Method untuk mengisi property $current_character_string,
     * $next_character_string, dan $prev_character_string.
     */
    protected function populateCurrentCharacter()
    {
        $x = $this->current_character;
        $ch = isset($this->raw[$x]) ? $this->raw[$x] : false;
        $nch = isset($this->raw[$x+1]) ? $this->raw[$x+1] : false;
        $pch = isset($this->raw[$x-1]) ? $this->raw[$x-1] : false;
        $this->current_character_string = $ch;
        $this->next_character_string = $nch;
        $this->prev_character_string = $pch;
    }

    /**
     * Method untuk mengubah property yang diset oleh method
     * self::populateCurrentCharacter().
     * Secara default karakter break "\r\n" akan dijadikan sebagai
     * satu karakter.
     */
    protected function manipulateCurrentCharacter()
    {
        $x = $this->current_character;
        $ch = $this->current_character_string;
        $nch = $this->next_character_string;
        if ($ch == "\r" && $nch == "\n") {
            $ch = "\r\n";
            $nch = isset($this->raw[$x+2]) ? $this->raw[$x+2] : false;
            $this->current_character_string = $ch;
            $this->next_character_string = $nch;
            $this->current_character++;
        }
    }

    /**
     * Gunakan method ini, untuk memberikan tanda true pada property.
     */
    protected function assignCurrentCharacter()
    {
        if ($this->next_character_string === false) {
            $this->is_last = true;
        }
        if (in_array($this->current_character_string, ["\r", "\n", "\r\n"])) {
            $this->is_break = true;
        }
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function beforeAnalyzeCurrentCharacter()
    {
    }

    /**
     * Gunakan method ini jika diperlukan.
     * @see
     *   self::looping()
     */
    protected function afterAnalyzeCurrentCharacter()
    {
    }

    /**
     * Method untuk mempersiapkan segala sesuatu sebelum
     * kembali looping ke karakter berikutnya.
     * Jika anda meng-override method ini, pastikan jalankan
     * parent::prepareNextLoop().
     */
    protected function prepareNextLoop()
    {
        if ($this->is_break && !$this->is_last) {
            $this->current_line++;
        }
        $this->current_character++;
        if ($this->is_break) {
            $this->current_column = 1;
        }
        else {
            $this->current_column++;
        }
    }

    /**
     * Gunakan method ini untuk menghapus tanda true pada property.
     */
    protected function resetAssignCharacter()
    {
        $this->is_break = false;
    }
}
