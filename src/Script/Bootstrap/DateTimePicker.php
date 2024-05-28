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

namespace NTLAB\JS\Script\Bootstrap;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;
use NTLAB\JS\Util\Asset;
use NTLAB\JS\Util\JSValue;

/**
 * Include Bootstrap DateTimePicker assets.
 *
 * @method string call(string $el, array $options = [])
 * @author Toha <tohenk@yahoo.com>
 */
class DateTimePicker extends Base
{
    protected function configure()
    {
        $this->setAsset(new Asset('bootstrap-datetimepicker', [
            Asset::ASSET_JAVASCRIPT => 'js',
            Asset::ASSET_STYLESHEET => 'css',
        ]));
        $this->addDependencies(['Bootstrap']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        $this->useJavascript('tempus-dominus.min');
        $this->useStylesheet('tempus-dominus.min');

        /** @var \NTLAB\JS\Script\Bootstrap\DateTimePickerLocaleCustom $script */
        $script = self::create('Bootstrap.DateTimePickerLocaleCustom');
        if (!$script->canUseLocale($locale = $this->getLocale(true))) {
            self::create('Bootstrap.DateTimePickerLocale')
                ->useLocaleJavascript(null, $locale);
        }
        self::create('Bootstrap.DateTimePickerPlugins')
            ->useJavascript('bi-one');

        return <<<EOF
// use bootstrap icons
tempusDominus.extend(tempusDominus.plugins.bi_one.load);
// set locale
const datetimepickerLocale = '{$locale}';
if (tempusDominus.locales && tempusDominus.locales[datetimepickerLocale]) {
    tempusDominus.loadLocale(tempusDominus.locales[datetimepickerLocale]);
    tempusDominus.locale(tempusDominus.locales[datetimepickerLocale].name);
}
$.fn.datetimepicker = function(options) {
    this.each(function() {
        options = options || {};
        const tdOptions = {};
        if (options.td) {
            Object.assign(tdOptions, options.td);
        }
        let format = tempusDominus.DefaultOptions.localization.dateFormats.L;
        if (options.withTime) {
            format += ' ' + tempusDominus.DefaultOptions.localization.dateFormats.LT;
        } else {
            Object.assign(tdOptions, {display: {components: {clock: false}}});
        }
        Object.assign(tdOptions, {localization: {format: format}});
        const picker = new tempusDominus.TempusDominus($(this)[0], tdOptions);
        $(this).data('dtpicker', picker);
    });
}
EOF;
    }

    /**
     * Call script.
     *
     * @param string $el        The element selector
     * @param array $options    The datepicker options
     */
    public function doCall($el, $options = [])
    {
        $options = JSValue::create((array) $options)->setInline(true);
        $this
            ->add(
                <<<EOF
$('$el').datetimepicker($options);
EOF
            );
    }
}
