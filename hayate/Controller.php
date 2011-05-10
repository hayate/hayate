<?php
/**
 * @author Andrea Belvedere <scieck@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 * date: Mon Apr 25 05:32:28 JST 2011
 */
namespace Hayate;

abstract class Controller extends Event
{
    const PreAction = 'PreAction';
    const PostAction = 'PostAction';

    public function __construct()
    {
        var_dump(__METHOD__);
        $this->register(self::PreAction, array($this, 'preAction'));
        $this->register(self::PostAction, array($this, 'postAction'));
    }
    protected function preAction() {}
    protected function postAction() {}
}
