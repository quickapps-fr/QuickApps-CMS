<?php
App::uses('Helper', 'View');
class AppHelper extends Helper {
    public $hooks = array();
    public $events = array();
    public $listeners = array();
    public $eventMap = array(); # function_name => Helper, useful for hookTags() searching
    public $helpers = array(
        'Layout',
        'Menu',        # menu helper
        'Form' => array('className' => 'QaForm'),
        'Html' => array('className' => 'QaHtml'),
        'Session',
        'Js'
    );
    public $Options = array(
        'break' => false,
        'breakOn' => false,
        'collectReturn' => false
    );
    private $__Options = array(
        'break' => false,
        'breakOn' => false,
        'collectReturn' => false
    );

    public function __construct(View $View, $settings = array()) {
        $this->__loadHooks();
        parent::__construct($View, $settings = array());
    }

    public function beforeRender() {
        $this->__loadHookEvents();
        return true;
    }

    public function attachModuleHooks($plugin) {
        $Plugin = Inflector::camelize($plugin);

        if (isset($this->listeners[$Plugin . 'Hook'])) {
            return;
        }

        $folder = new Folder;
        $folder->path = CakePlugin::path($Plugin) . 'View' . DS . 'Helper' . DS;
        $files = $folder->find('(.*)Hook(Helper)\.php');

        foreach ($files as $helper) {
            $helper = str_replace('Helper.php', '', $helper);
            $this->hooks[] = "{$Plugin}.{$helper}";
            $this->$helper = $this->_View->loadHelper("{$Plugin}.{$helper}" , array('plugin' => $plugin));

            if (!is_object($this->{$helper})) {
                continue;
            }

            $methods = array();
            $_methods = get_this_class_methods($this->{$helper});

            foreach ($_methods as $method) {
                $methods[] = $method;
            }

            $this->listeners[$helper] = $methods;
            $this->events = array_merge($this->events, $methods);
        }
    }

    public function deattachModuleHooks($plugin) {
        $Plugin = Inflector::camelize($plugin);

        foreach ($this->hooks as $hk => $hook) {
            if (strpos($hook, "{$Plugin}.") === false) {
                continue;
            }

            $Hook = str_replace("{$Plugin}.", '', $hook);

            foreach ($this->listeners[$Hook] as $event) {
                unset($this->events[array_search($event, $this->events)]);
            }

            unset($this->hooks[$hk]);
            unset($this->listeners[$Hook]);
            unset($this->{$Hook});
        }
    }

/**
 * Chech if hook exists
 *
 * @param string $hook Name of the hook to check
 * @return boolean
 */
    public function hook_defined($hook) {
        return ( in_array($hook, $this->events) == true );
    }

/**
 * Trigger a callback method on every HookHelper.
 *
 * ### Options
 *
 * - `breakOn` Set to the value or values you want the callback propagation to stop on.
 *    Can either be a scalar value, or an array of values to break on.
 *    Defaults to `false`.
 *
 * - `break` Set to true to enabled breaking. When a trigger is broken, the last returned value
 *    will be returned.  If used in combination with `collectReturn` the collected results will be returned.
 *    Defaults to `false`.
 *
 * - `collectReturn` Set to true to collect the return of each object into an array.
 *    This array of return values will be returned from the hook() call. Defaults to `false`.
 *
 * - `alter` Allows each callback gets called on to modify the parameters to the next object.
 *    Defaults to true.
 *
 * @param string $event name of the hook to call
 * @param mixed $data data for the triggered callback
 * @param array $option Array of options
 * @return mixed Either the last result or all results if collectReturn is on. Or null in case of no response
 */
    public function hook($event, &$data = array(), $options = array()) {
        $event = Inflector::underscore($event);
        $result = $this->__dispatchEvent($event, $data, $options);
        return $result;
    }

/**
 * Overwrite default options for Hook dispatcher.
 * Useful when calling a hook with non-parameter and custom options.
 *
 * Watch out!: Hook dispatcher automatic reset its default options to
 * the original ones after `hook()` is invoked.
 * Means that if you need to call more than one hook (consecutive) with no parameters and
 * same options ** you must call `setHookOptions()` after each hook() **
 *
 * ### Usage
 * For example in any controller action:
 * {{{
 *  $this->setHookOptions(array('collectReturn' => false));
 *  $response = $this->hook('collect_hook_with_no_parameters');
 *
 *  $this->setHookOptions(array('collectReturn' => false, 'break' => true, 'breakOn' => false));
 *  $response2 = $this->hook('OTHER_collect_hook_with_no_parameters');
 * }}}
 *
 * @param array $options Array of options to overwrite
 * @return void
 */
    public function setHookOptions($options) {
        $this->Options = Set::merge($this->Options, $options);
    }

/**
 * Evaluate a string of PHP code.
 *
 * This is a wrapper around PHP's eval(). It uses output buffering to capture both
 * returned and printed text. Unlike eval(), we require code to be surrounded by
 * <?php ?> tags; in other words, we evaluate the code as if it were a stand-alone
 * PHP file.
 *
 * Using this wrapper also ensures that the PHP code which is evaluated can not
 * overwrite any variables in the calling code, unlike a regular eval() call.
 *
 * @param string $code The code to evaluate.
 * @return
 *   A string containing the printed output of the code, followed by the returned
 *   output of the code.
 *
 */
    protected function _php_eval($code) {
        ob_start();
        $Layout =& $this->_View->viewVars['Layout'];
        $View =& $this->_View;
        print eval('?>' . $code);
        $output = ob_get_contents();
        ob_end_clean();

        return (bool)$output;
    } 

/**
 * Check if a path matches any pattern in a set of patterns.
 *
 * @param $path The path to match.
 * @param $patterns String containing a set of patterns separated by \n, \r or \r\n.
 * @return Boolean value: TRUE if the path matches a pattern, FALSE otherwise.
 */
    protected function _urlMatch($patterns, $path = false) {
        if (empty($patterns)) {
            return false;
        }

        $path = !$path ? '/' . $this->_View->request->url : $path;
        $patterns = explode("\n", $patterns);

        foreach ($patterns as &$p) {
            $p = Router::url('/') . $p;
            $p = str_replace('//', '/', $p);
            $p = str_replace($this->_View->base, '', $p);
        }

        $patterns = implode("\n", $patterns);

        // Convert path settings to a regular expression.
        // Therefore replace newlines with a logical or, /* with asterisks and the <front> with the frontpage.
        $to_replace = array(
            '/(\r\n?|\n)/', // newlines
            '/\\\\\*/',     // asterisks
            '/(^|\|)\/($|\|)/' // front '/'
        );

        $replacements = array(
            '|',
            '.*',
            '\1' . preg_quote(Router::url('/'), '/') . '\2'
        );

        $patterns_quoted = preg_quote($patterns, '/');
        $regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';

        return (bool) preg_match($regexps[$patterns], $path);
    }

/**
 * Dispatch Helper-hooks from all the plugins and core
 *
 * @see AppHelper::hook()
 * @return mixed Either the last result or all results if collectReturn is on. Or NULL in case of no response
 */
    private function __dispatchEvent($event, &$data = array(), $options = array()) {
        $options = array_merge($this->Options, (array)$options);
        $collected = array();

        if (!$this->hook_defined($event)) {
            $this->__resetOptions();

            return null;
        }

        foreach ($this->listeners as $object => $methods) {
            foreach ($methods as $method) {
                if ($method == $event && is_callable(array($this->{$object}, $method))) {
                    $result = $this->{$object}->$event($data);

                    if ($options['collectReturn'] === true) {
                        $collected[] = $result;
                    }

                    if ($options['break'] && ($result === $options['breakOn'] ||
                        (is_array($options['breakOn']) && in_array($result, $options['breakOn'], true)))
                    ) {
                        $this->__resetOptions();

                        return $result;
                    }
                }
            }
        }

        if (empty($collected) && in_array($result, array('', null), true)) {
            $this->__resetOptions();

            return null;
        }

        $this->__resetOptions();

        return $options['collectReturn'] ? $collected : $result;
    }

    private function __resetOptions() {
        if ($this->Options !== $this->__Options) {
            $this->Options = $this->__Options;
        }
    }

    private function __loadHookEvents() {
        $eventMap = array();

        foreach ($this->helpers as $helper) {
            if (is_array($helper)) {
                continue;
            }

            $helper = strpos($helper, '.') !== false ? substr($helper, strpos($helper, '.')+1) : $helper;

            if (strpos($helper, 'Hook') !== false) {
                if (!is_object($this->{$helper})) {
                    continue;
                }

                $methods = array();
                $_methods = get_this_class_methods($this->{$helper});

                foreach ($_methods as $method) {
                    $methods[] = $method;
                    $eventMap[$method] = (string)$helper;
                }

                $this->listeners[$helper] = $methods;
                $this->events = array_merge($this->events, $methods);
                $this->eventMap = array_merge($this->eventMap, $eventMap);
            }
        }
    }

    private function __loadHooks() {
        if ($hooks = Configure::read('Hook.helpers')) {
            foreach ($hooks as $hook) {
                if (strpos($hook, '.') !== false) {
                    $hookE = explode('.', $hook);
                    $plugin = $hookE[0];
                    $hookHelper = $hookE[1];
                    $filePath = App::pluginPath($plugin) . 'View' . DS . 'Helper' . DS . "{$hookHelper}Helper" . '.php';
                } else {
                    $filePath = APP . 'View' . DS . 'Helper' . DS . "{$hook}Helper.php";
                }

                if (file_exists($filePath)) {
                    $this->hooks[] = $hook;
                    $this->helpers[] = $hook;
                }
            }
        }

        $this->helpers = array_unique($this->helpers);
    }
}