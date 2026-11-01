<?php

declare(strict_types=1);

namespace WebBase\Core\support\middleware;

use think\Request;

/** Reject structured language input before the framework's string parser. */
class LoadLangPack extends \think\middleware\LoadLangPack
{
    protected function detect(Request $request): string
    {
        foreach ([
            $request->get($this->config['detect_var']),
            $request->header($this->config['header_var']),
            $request->cookie($this->config['cookie_var']),
        ] as $candidate) {
            if ($candidate !== null && !is_string($candidate)) {
                return $this->lang->getLangSet();
            }
        }

        return parent::detect($request);
    }
}
