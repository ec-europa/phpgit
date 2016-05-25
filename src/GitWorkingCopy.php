<?php
namespace PhpGit {


    /**
     * Small class to interact with a git working copy directory
     * Plan :
     * $gitRepo = new GitRemoteRepo($path);
     * $gitWorkingCopy = $gitRepo->clone($workingCopyPath);
     * // and also :
     * $gitWorkingCopy->getRemoteRepo($name);
     * Class GitWorkingCopy
     * @package PhpGit 
     */
    class GitWorkingCopy {

        var $workingCopyPath_;
        var $gitBinary_ = 'git';
        var $config_ = array();

        public function __construct($path) {
            if(!is_dir($path.DIRECTORY_SEPARATOR.'.git'))
                throw new \Exception($path.' is not a working copy git project.');
            $this->config_ = parse_ini_file($path.DIRECTORY_SEPARATOR.'.git/config',true);
            $this->workingCopyPath_ = $path;
        }

        public function add($path) {
            $arg = '';
            if(is_array($path)) {
                foreach ($path as $file) {
                    $arg .= ' '.escapeshellarg($file);
                }
            } else {
                $arg .= escapeshellarg($path);
            }
            return $this->runGit(sprintf(' add %s',$arg));
        }

        public function cancel($path) {
            $arg = '';
            if(is_array($path)) {
                foreach ($path as $file) {
                    $arg .= ' '.escapeshellarg($file);
                }
            } else {
                $arg .= escapeshellarg($path);
            }
            return $this->runGit(sprintf(' reset %s',$arg));
        }

        public function commit($message) {
            $message = escapeshellarg($message);
            return $this->runGit(sprintf(' commit --allow-empty -m %s',$message));
        }

        public function mv($source,$target) {
            $source = escapeshellarg($source);
            $target = escapeshellarg($target);
            return $this->runGit(sprintf(' mv %s %s',$source,$target));
        }

        public function rm($path,$recursive=false,$force=false) {
            $path = escapeshellarg($path);
            $recursive = $recursive ? ' -r ' : '';
            $force = $force ? ' -f ' : '';

            return $this->runGit(sprintf(' rm %s %s %s',$recursive,$force,$path));
        }

        public function pull($remote=null,$ref=null) {
            $remote = is_null($remote) ? '' : escapeshellarg($remote);
            $ref = is_null($ref) ? '' : escapeshellarg($ref);
            return $this->runGit(sprintf(' pull %s %s',$remote,$ref));
        }

        public function push($remote=null,$ref=null) {
            $remote = is_null($remote) ? '' : escapeshellarg($remote);
            $ref = is_null($ref) ? '' : escapeshellarg($ref);
            return $this->runGit(sprintf(' push %s %s',$remote,$ref));
        }

        public function fetch($remote=null,$ref=null) {
            $remote = is_null($remote) ? '' : escapeshellarg($remote);
            $ref = is_null($ref) ? '' : escapeshellarg($ref);
            return $this->runGit(sprintf(' fetch %s %s',$remote,$ref));
        }

        public function getRemotes() {
            return $this->getConfigList('remote');
        }

        public function getBranches() {
            return $this->getConfigList('branch');
        }

        public function getBranch($name) {
            $branches = $this->getBranches();
            if(!isset($branches[$name]))
                return false;
            return $branches[$name];
        }

        public function getTags() {
            //most complicated wrapper ever :
            return $this->runGit(' tag');
        }

        public function getConfigList($object) {
            $objects = array();
            foreach($this->config_ as $configSectionKey => $configSection) {
                $configParts = explode(' ',$configSectionKey);
                if(count($configParts) < 2 ) continue;
                list($objectName,$idRaw) = $configParts;
                if ($objectName != $object) continue;
                //remove " for start and end
                $id = trim($idRaw,'"');
                $objects[$id] = $configSection;
            }
            return $objects;
        }


        private function runGit($arguments) {
            chdir($this->workingCopyPath_);
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
