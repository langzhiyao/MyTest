;(function(window) {

  var svgSprite = '<svg>' +
    '' +
    '<symbol id="icon-31shouye" viewBox="0 0 1024 1024">' +
    '' +
    '<path d="M939.175079 484.343103 557.721321 107.755166c-1.927909-2.290159-5.950526-6.025227-11.568477-9.730619-10.06217-6.627954-20.757766-10.001795-31.783891-10.001795-11.855002 0-22.820752 3.40454-32.597419 10.092869-6.477528 4.458545-9.942443 8.465812-11.4334 10.454096L89.606831 484.373803c-6.522554 6.417153-10.107195 14.973016-10.107195 24.071232 0 9.06854 3.584642 17.624403 10.107195 24.041556 11.990079 11.900028 29.494755 8.495488 46.169529-7.92346l25.592888-25.306362 354.068038-342.966189 2.15406 2.109034 375.428531 369.146455c11.508102 7.531534 21.842471 11.538801 30.217209 11.538801 6.236028 0 11.44875-2.169409 15.936971-6.598278 6.507204-6.417153 10.092869-14.94334 10.122545-24.041556C949.297624 499.346819 945.712983 490.789932 939.175079 484.343103z"  ></path>' +
    '' +
    '<path d="M796.885376 464.880843c-15.72617 0-28.590152 12.68388-28.681226 28.259624l0 351.311251c0 19.191084-15.816221 34.796504-35.278481 34.796504l-87.458911 0L645.466758 683.423231c0-48.143487-39.617299-87.308485-88.317465-87.308485l-85.531002 0c-48.700165 0-88.317465 39.164998-88.317465 87.308485L383.300826 879.248222 295.841915 879.248222c-19.462261 0-35.30918-15.60542-35.30918-34.796504L260.532735 493.230518c-0.029676-15.636119-12.863982-28.349675-28.620851-28.349675-15.741519 0-28.605501 12.713556-28.650527 28.318976l0 355.468944c0 48.143487 39.617299 87.308485 88.317465 87.308485l148.993382 0L440.572204 687.670975c0-19.160385 15.84692-34.736129 35.30918-34.736129l77.020164 0c19.47761 0 35.293831 15.575744 35.293831 34.736129l0 248.307296 149.008732 0c48.685839 0 88.332814-39.164998 88.332814-87.308485L825.536926 493.170143C825.415153 477.594399 812.580846 464.880843 796.885376 464.880843z"  ></path>' +
    '' +
    '</symbol>' +
    '' +
    '</svg>'
  var script = function() {
    var scripts = document.getElementsByTagName('script')
    return scripts[scripts.length - 1]
  }()
  var shouldInjectCss = script.getAttribute("data-injectcss")

  /**
   * document ready
   */
  var ready = function(fn) {
    if (document.addEventListener) {
      if (~["complete", "loaded", "interactive"].indexOf(document.readyState)) {
        setTimeout(fn, 0)
      } else {
        var loadFn = function() {
          document.removeEventListener("DOMContentLoaded", loadFn, false)
          fn()
        }
        document.addEventListener("DOMContentLoaded", loadFn, false)
      }
    } else if (document.attachEvent) {
      IEContentLoaded(window, fn)
    }

    function IEContentLoaded(w, fn) {
      var d = w.document,
        done = false,
        // only fire once
        init = function() {
          if (!done) {
            done = true
            fn()
          }
        }
        // polling for no errors
      var polling = function() {
        try {
          // throws errors until after ondocumentready
          d.documentElement.doScroll('left')
        } catch (e) {
          setTimeout(polling, 50)
          return
        }
        // no errors, fire

        init()
      };

      polling()
        // trying to always fire before onload
      d.onreadystatechange = function() {
        if (d.readyState == 'complete') {
          d.onreadystatechange = null
          init()
        }
      }
    }
  }

  /**
   * Insert el before target
   *
   * @param {Element} el
   * @param {Element} target
   */

  var before = function(el, target) {
    target.parentNode.insertBefore(el, target)
  }

  /**
   * Prepend el to target
   *
   * @param {Element} el
   * @param {Element} target
   */

  var prepend = function(el, target) {
    if (target.firstChild) {
      before(el, target.firstChild)
    } else {
      target.appendChild(el)
    }
  }

  function appendSvg() {
    var div, svg

    div = document.createElement('div')
    div.innerHTML = svgSprite
    svgSprite = null
    svg = div.getElementsByTagName('svg')[0]
    if (svg) {
      svg.setAttribute('aria-hidden', 'true')
      svg.style.position = 'absolute'
      svg.style.width = 0
      svg.style.height = 0
      svg.style.overflow = 'hidden'
      prepend(svg, document.body)
    }
  }

  if (shouldInjectCss && !window.__iconfont__svg__cssinject__) {
    window.__iconfont__svg__cssinject__ = true
    try {
      document.write("<style>.svgfont {display: inline-block;width: 1em;height: 1em;fill: currentColor;vertical-align: -0.1em;font-size:16px;}</style>");
    } catch (e) {
      console && console.log(e)
    }
  }

  ready(appendSvg)


})(window)