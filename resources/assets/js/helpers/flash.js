import { scrollTo } from './scrollTo';

export default {
	state: {
		success: null,
		error: null
	},
	setSuccess(message) {
		this.state.success = message;
		setTimeout(() => {
			this.removeSuccess()
		}, 3000);
	},
	setError(message) {
		this.state.error = message;
        // this.scrollTo(document.body, 0, 600);
        scrollTo(0, 1500, 'easeInOutQuint');
		setTimeout(() => {
			this.removeError()
		}, 6000);
	},
	removeSuccess() {
		this.state.success = null
	},
	removeError() {
		this.state.error = null
	},
    // scrollToY(scrollTargetY, speed, easing) {
    //     // scrollTargetY: the target scrollY property of the window
    //     // speed: time in pixels per second
    //     // easing: easing equation to use
    //
    //     var scrollY = window.scrollY || document.documentElement.scrollTop,
    //         scrollTargetY = scrollTargetY || 0,
    //         speed = speed || 2000,
    //         easing = easing || 'easeOutSine',
    //         currentTime = 0;
    //
    //     // min time .1, max time .8 seconds
    //     var time = Math.max(.1, Math.min(Math.abs(scrollY - scrollTargetY) / speed, .8));
    //
    //     // easing equations from https://github.com/danro/easing-js/blob/master/easing.js
    //     var easingEquations = {
    //         easeOutSine: function (pos) {
    //             return Math.sin(pos * (Math.PI / 2));
    //         },
    //         easeInOutSine: function (pos) {
    //             return (-0.5 * (Math.cos(Math.PI * pos) - 1));
    //         },
    //         easeInOutQuint: function (pos) {
    //             if ((pos /= 0.5) < 1) {
    //                 return 0.5 * Math.pow(pos, 5);
    //             }
    //             return 0.5 * (Math.pow((pos - 2), 5) + 2);
    //         }
    //     };
    //
    //     // add animation loop
    //     function tick() {
    //         currentTime += 1 / 60;
    //
    //         var p = currentTime / time;
    //         var t = easingEquations[easing](p);
    //
    //         if (p < 1) {
    //             requestAnimFrame(tick);
    //
    //             window.scrollTo(0, scrollY + ((scrollTargetY - scrollY) * t));
    //         } else {
    //             console.log('scroll done');
    //             window.scrollTo(0, scrollTargetY);
    //         }
    //     }
    //
    //     // call it once to get started
    //     tick();
    // }
}
