<?php
namespace EasyFast\Bash;

abstract class ParentBash
{

    /**
     * getArgsVars
     * @param $argv
     * @param $argc
     */
    public function getArgsVars($argv, $argc)
    {
        for ($i=1; $i < $argc; $i++) {parse_str($argv[$i]);}
    }

    /**
     * readStdin
     * @param $prompt
     * @param $valid_inputs
     * @param string $default
     * @return string
     */
    public function readStdin($prompt, $valid_inputs, $default = '')
    {
        while (!isset($input) || (is_array($valid_inputs) && !in_array($input, $valid_inputs)) || ($valid_inputs == 'is_file' && !is_file($input))) {
            echo $prompt;
            $input = trim(fgets(STDIN));
            if (empty($input) && !empty($default)) {
                $input = $default;
            }
        }
        return $input;
    }
}