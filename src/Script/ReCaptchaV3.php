<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace NTLAB\JS\Script;

use NTLAB\JS\Script as Base;
use NTLAB\JS\Repository;

/**
 * Include Google ReCaptcha V3 assets.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class ReCaptchaV3 extends Base
{
    protected function configure()
    {
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $lang = $this->getLocale(true);
        $key = $this->getOption('site-key');
        $this->useJavascript(sprintf('https://www.google.com/recaptcha/api.js?render=%s&hl=%s', $key, $lang));

        return <<<EOF
grecaptcha.my = {
    key: '$key',
    ids: {},
    add: function(id, action, auto = true) {
        this.ids[id] = {id: id, action: action, auto: auto};
    },
    init: function(id) {
        const self = this;
        if (id === undefined || id === null) {
            id = [];
            for (const n in self.ids) {
                if (self.ids[n].auto) {
                    id.push(n);
                }
            }
        }
        if (typeof id === 'string') {
            id = [id];
        }
        if (id.length) {
            grecaptcha.ready(() => {
                for (let i = 0; i < id.length; i++) {
                    const cid = id[i];
                    const action = self.ids[cid].action;
                    grecaptcha.execute(self.key, {action: action})
                        .then(token => {
                            $('#' + cid).val(token);
                        });
                }
            });
        }
    }
}
EOF;
    }

    public function getInitScript()
    {
        $this
            ->add(
                <<<EOF
(() => {
    grecaptcha.my.init();
    const captchas = Object.keys(grecaptcha.my.ids);
    for (let i = 0; i <= captchas.length; i++) {
        const cid = captchas[i];
        $('#' + cid).parents('form').on('formerror', () => {
            grecaptcha.my.init(cid);
        });
    }
})();
EOF
            );
    }
}
