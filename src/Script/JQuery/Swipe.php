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

namespace NTLAB\JS\Script\JQuery;

use NTLAB\JS\Script\JQuery as Base;
use NTLAB\JS\Repository;

/**
 * Provide swipe support to an element.
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Swipe extends Base
{
    protected function configure()
    {
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    public function getScript()
    {
        return <<<EOF
// https://gist.github.com/SleepWalker/da5636b1abcbaff48c4d
$.swiper = function(el, options) {
    const swipe = {
        vertical: true,
        horizontal: true,
        transitionTime: '0.5s',
        transitionEffect: 'ease',
        touchstartX: 0,
        touchstartY: 0,
        touchendX: 0,
        touchendY: 0,
        updateTransition: function(prop) {
            const self = this;
            const transition = [prop, self.transitionTime, self.transitionEffect].join(' ');
            if (self.el.style.transition.indexOf(transition) < 0) {
                if (self.el.style.transition === '') {
                    self.el.style.transition = transition;
                } else {
                    self.el.style.transition = [self.el.style.transition, transition].join(', ');
                }
            }
        },
        calcPosition: function(w1, w2, p1, p2, d) {
            const r = w2 - w1;
            const dist = p1 + d;
            if (d > 0 && dist > 0) {
                d -= dist;
            }
            if (d < 0 && dist < -r) {
                d -= dist + r;
            }
            let pos = p2 + d;
            return pos + 'px';
        },
        checkPosition: function(w1, w2, p1, p2, d) {
            const self = this;
            if (w2 > w1) {
                return self.calcPosition(w1, w2, p1, p2, d);
            }
        },
        adjustPosition: function(dx = null, dy = null) {
            const self = this;
            let c, p;
            if (dx !== null && dx !== undefined) {
                dx = parseInt(dx);
                c = parseInt(self.el.style.left) || 0;
                p = self.checkPosition(self.el.offsetParent.clientWidth, self.el.offsetWidth, self.el.offsetLeft, c, dx);
                if (!p) {
                    p = self.checkPosition(self.el.parentElement.clientWidth, self.el.scrollWidth, c, c, dx);
                }
                if (p && self.el.style.left !== p) {
                    self.updateTransition('left');
                    self.el.style.left = p;
                }
            }
            if (dy !== null && dy !== undefined) {
                dy = parseInt(dy);
                c = parseInt(self.el.style.top) || 0;
                p = self.checkPosition(self.el.offsetParent.clientHeight, self.el.offsetHeight, self.el.offsetTop, c, dy);
                if (!p) {
                    p = self.checkPosition(self.el.parentElement.clientHeight, self.el.scrollHeight, c, c, dx);
                }
                if (p && self.el.style.top !== p) {
                    self.updateTransition('top');
                    self.el.style.top = p;
                }
            }
        },
        handleGesture: function(resize = null) {
            const self = this;
            let delta;
            if (self.horizontal) {
                if (!resize) {
                    delta = self.touchendX - self.touchstartX;
                    if (delta !== 0) {
                        self.adjustPosition(delta, null);
                    }
                } else {
                    if (self.el.style.left) {
                        self.el.style.left = null;
                    }
                }
            }
            if (self.vertical) {
                if (!resize) {
                    delta = self.touchendY - self.touchstartY;
                    if (delta !== 0) {
                        self.adjustPosition(null, delta);
                    }
                } else {
                    if (self.el.style.top) {
                        self.el.style.top = null;
                    }
                }
            }
        },
        init: function(el, options) {
            options = options || {};
            const self = this;
            if (el instanceof HTMLElement) {
                Object.keys(self).forEach(function(prop) {
                    if (typeof self[prop] !== 'function' && typeof options[prop] !== 'undefined') {
                        self[prop] = options[prop];
                    }
                });
                self.el = el;
                self.el.addEventListener('touchstart', function(e) {
                    self.touchstartX = e.changedTouches[0].screenX;
                    self.touchstartY = e.changedTouches[0].screenY;
                }, false);
                self.el.addEventListener('touchend', function(e) {
                    self.touchendX = e.changedTouches[0].screenX;
                    self.touchendY = e.changedTouches[0].screenY;
                    self.handleGesture();
                }, false);
                if (!$.swiper.instances) {
                    $.swiper.instances = [];
                    window.addEventListener('resize', function(e) {
                        $.swiper.instances.forEach(function(swiper) {
                            if (swiper.el) {
                                swiper.handleGesture(true);
                            }
                        });
                    });
                }
                $.swiper.instances.push(self);
                return self;
            }
        }
    }
    return swipe.init(el, options);
}
EOF;
    }
}
