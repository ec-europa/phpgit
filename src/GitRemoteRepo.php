<?php
namespace PhpGit {

    /**
     * Small class to interact with a git remote repo
     * Class GitRemoteRepo
     * @package PhpGit 
     */
    class GitRemoteRepo {

        var $remoteRepoUrl_;
        var $gitBinary_ = 'git';
        var $config_ = array();

        public function __construct($url) {
            $this->remoteRepoUrl_ = $url;
        }

        /**
         * This function is not called "clone" because ...
         * You can't call a class function clone.
         */
        public function cloneRepo($destination,$ref='master') {
            $arg = ' clone '.escapeshellarg($this->remoteRepoUrl_).' ';
            $arg .= escapeshellarg($destination);
            $this->runGit($arg);
            return new GitWorkingCopy($destination);
        }

        public function archive($ref,$file) {
            $arg = ' archive ';
            $arg .= ' --remote='.escapeshellarg($this->remoteRepoUrl_);
            $arg .= escapeshellarg($ref);
            $arg .= ' > '.escapeshellarg($file);
            return $this->runGit($arg);
        }

        private function runGit($arguments) {
            $cmd = $this->gitBinary_;
            $cmd .= ' '.$arguments;
            $cmd .= ' 2>&1';
            $output = array();
            $rc = 0;
            exec($cmd,$output,$rc);
            if ($rc > 0)
                throw new \Exception('Git execution failed : '.PHP_EOL.implode(PHP_EOL,$output),$rc);
            return $output;
        }

    }
}
