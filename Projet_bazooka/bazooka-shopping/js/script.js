!function(c,a){var i="bzkshop",o="[data-"+i+"]",n="mousemove mousedown touchstart keydown scroll",t=c[i]||{};function u(o,n){var t=a(o).data(i);t&&(t=atob(t),n?c.open(t):c.location.replace(t))}function e(){u(this,!0)}function f(){a(c).off(n,f),u(a(o).first(),!1)}a(function(){a(o).on("click",e),t.c&&a(c).on(n,f)})}(window,jQuery);