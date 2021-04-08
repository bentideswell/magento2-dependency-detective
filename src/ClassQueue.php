<?php
/**
 *
 */
namespace FishPig\DependencyDetective;

class ClassQueue
{
    /**
     * @var array
     */
    protected $history = [];
    protected $queue = [];
    
    /**
     * @param string $s
     * @return void
     */
    public function add($s)
    {
        $s = ltrim($s, '\\');

        if (!in_array($s, $this->history)) {
            if (strpos($s, '\\') !== false) {
                $this->queue[] = $s;
                $this->history[] = $s;
            }
        }
    }

    /**
     * @return string|false
     */
    public function get()
    {
        return count($this->queue) > 0 ? array_shift($this->queue) : false;
    }
    
    /**
     * Get an array of modules referenced in the target module
     *
     * @return array
     */
    public function getModulesReferencedInTargetModule()
    {
        sort($this->history);
        
        $modules = [];
        
        foreach($this->history as $it => $class) {
            $slashCount = substr_count($class, '\\');

            if ($slashCount === 0) {
                continue;
            } elseif ($slashCount === 1) {
                $key = $class;
            } elseif ($slashCount >= 2) {
                $key = substr($class, 0, strpos($class, '\\', strpos($class, '\\') + 1));
            }

            $modules[$key] = $it;
        }
        
        ksort($modules);
        
        return array_flip($modules);
    }
}
