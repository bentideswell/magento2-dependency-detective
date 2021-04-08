<?php
/**
 *
 */
namespace FishPig\DependencyDetective;

class ClassFileParser
{
    /**
     * @var array
     */
    protected $cache = [];
    
    /**
     * @param string $data
     * @return string
     */
    public function getNamespace($data) : string
    {
        return $this->_cache('namespace', $data, function($data) {
            if (preg_match('/namespace\s+([a-zA-Z0-9_\\\]+);\n/iU', $data, $match)) {
                return $match[1];
            }
            
            return false;
        });
    }

    /**
     * @param string $data
     * @return string
     */
    public function getClassName($data) : string
    {
        return $this->_cache('class_name', $data, function($data) {
            if (preg_match(
                '/\n(abstract\s?)?(class)\s?(?<class_name>[a-zA-Z0-9_]+)[\s|{]+/', 
                $data, 
                $match
            )) {
                return $match['class_name'];
            }
            
            return false;
        });
    }

    /**
     * @param string $data
     * @return array|false
     */
    public function getUseArray($data)
    {
        return $this->_cache('uses', $data, function($data) {
            $classes = [];
    
            if (preg_match_all('/\nuse (.*);/', $data, $uses)) {
                foreach ($uses[1] as $use) {
                    if (preg_match('/^(?<class_name>.*)\s+as\s+(?<alias>.*)$/', $use, $useMatch)) {
                        $classes[$useMatch['alias']] = $useMatch['class_name'];
                    } else {
                        $classes[] = $use;
                    }
                }
            }

            return $classes;
        });
    }
    
    /**
     * @param string $data
     * @return string|false
     */
    public function getExtends($data)
    {
        return $this->_cache('extends', $data, function($data) {
            $className = $this->getClassName($data);
    
            if (preg_match('/class[\s]+' . $className . '[\s]+extends[\s]+(.*)[,\s]+/iU', $data, $match)) {
                $extends = $match[1];
                
                if (strpos($extends, '\\') === 0) {
                    return $extends;
                }
        
                if (strpos($extends, '\\') === false) {
                    if ($uses = $this->getUseArray($data)) {
                        if (isset($uses[$extends])) {
                            return $uses[$extends];
                        }
                        
                        foreach ($uses as $use) {
                            if (preg_match('/\\\\' . preg_quote($extends, '/') . '$/', $use)) {
                                return $use;
                            }
                        }
                    }
                }
        
                return  $this->getNamespace($data) . '\\' . $extends; 
            }
            
            return false;
        });
    }
    
    /**
     * @param string $type
     * @param string $data
     * @param \Closure $callback
     * @return mixed
     */
    private function _cache($type, $data, $callback)
    {
        $hash = md5($data);
        
        if (!isset($this->cache[$type])) {
            $this->cache[$type] = [];
        }
        
        if (!isset($this->cache[$type][$hash])) {
            $this->cache[$type][$hash] = $callback($data);
        }
        
        return $this->cache[$type][$hash];
    }
}
