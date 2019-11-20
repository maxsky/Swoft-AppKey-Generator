<?php declare(strict_types=1);

namespace MaxSky\Console\Command;

use Exception;
use Swoft\Console\Advanced\Interact\Confirm;
use Swoft\Console\Annotation\Mapping\{Command, CommandMapping, CommandOption};
use Swoft\Console\Input\Input;
use Swoft\Console\Output\Output;

/**
 * This is description for the command group
 *
 * @Command(name="key", coroutine=false)
 */
class KeyGenerateCommand {

    /** @var Output */
    private $output;

    /**
     * this is description for the command
     *
     * @CommandMapping(name="generate", alias="gen", usage="key:generate", desc="Set the application key.")
     * @CommandOption(short="-s", name="--show", desc="Display the key instead of modifying files.", type="")
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     * @throws Exception
     *
     * @example
     *   {fullCommand} -h
     *   {fullCommand} --show
     */
    public function generate(Input $input, Output $output): int {
        $key = $this->generateRandomKey();

        if ($input->getOpt('s', false) || $input->getOpt('show', false)) {
            $styled = "<comment>{$key}</comment>";
            $output->writeln($styled);
            return 0;
        }

        $this->output = $output;

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if ($this->setKeyInEnvironmentFile($key)) {
            $styled = "<info>Application key [{$key}] set successfully.</info>";
            $output->writeln($styled);
        }
        return 0;
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     * @throws Exception
     */
    private function generateRandomKey() {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $key
     *
     * @return bool
     */
    private function setKeyInEnvironmentFile($key) {
        $currentKey = getenv('APP_KEY');

        $keyLen = strlen($currentKey);

        if ($keyLen !== 0) {
            if (Confirm::not("APP_KEY exist! This command will replace current key!\nDo you really wish to run this command?", false)) {
                return false;
            }
            $this->output->warning("Old APP_KEY is: {$currentKey}, please backup if you need.");
        }
        $this->writeNewEnvironmentFileWith($key);

        return true;
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param string $key
     *
     * @return void
     */
    private function writeNewEnvironmentFileWith($key) {
        $env_path = alias('@base/.env');
        file_put_contents($env_path, preg_replace(
            $this->keyReplacementPattern(), 'APP_KEY=' . $key, file_get_contents($env_path)
        ));
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    private function keyReplacementPattern() {
        $escaped = preg_quote('=' . getenv('APP_KEY'), '/');

        return "/^APP_KEY{$escaped}/m";
    }
}
