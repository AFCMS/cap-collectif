/*eslint-disable */
// Sometimes an iframe import babel-polyfill (eg: typeform)
// This trick avoid multiple babel-polyfill loaded
if (!global._babelPolyfill) {
  require('babel-polyfill');
}
require('./registration');
const config = require('./config');
global.Cookies = require('js-cookie');

require('fancybox')($);

// Our global App for symfony
const App = ($ => {
  const equalheight = container => {
    let currentTallest = 0;
    let currentRowStart = 0;
    const rowDivs = [];
    let topPosition = 0;

    $(container).each((index, el) => {
      const $el = $(el);
      $el.height('auto');
      topPosition = $el.position().top;

      if ($(window).width() > 767) {
        let currentDiv = 0;

        if (currentRowStart !== topPosition) {
          for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
            rowDivs[currentDiv].height(currentTallest);
          }
          rowDivs.length = 0; // empty the array
          currentRowStart = topPosition;
          currentTallest = $el.height();
          rowDivs.push($el);
        } else {
          rowDivs.push($el);
          currentTallest = currentTallest < $el.height() ? $el.height() : currentTallest;
        }
        for (currentDiv = 0; currentDiv < rowDivs.length; currentDiv++) {
          rowDivs[currentDiv].height(currentTallest);
        }
      }
    });
  };

  const resized = el => {
    const $el = $(el);

    $(window).resize(() => {
      equalheight($el);
    });
  };

  const customModal = el => {
    const $el = $(el);

    $el.appendTo('body');
  };

  const pieChart = () => {
    if (typeof google !== 'undefined') {
      google.load('visualization', '1', { packages: ['corechart'] });
      google.setOnLoadCallback(() => {
        $('.has-chart').googleCharts();
      });
    }
  };

  const video = el => {
    const $el = $(el);
    $el.on('click', e => {
      $.fancybox({
        href: e.currentTarget.href,
        type: $(e.currentTarget).data('type'),
        padding: 0,
        margin: 50,
        maxWidth: 1280,
        maxHeight: 720,
        fitToView: false,
        width: '90%',
        height: '90%',
      });
      return false;
    }); // on
  };

  const checkButton = el => {
    const $el = $(el);

    $($el).on('change', e => {
      const test = $(e.currentTarget).val();
      if (test === 0) {
        $('.block_media').hide();
        $('.block_link').toggle();
      } else if (test === 1) {
        $('.block_media').toggle();
        $('.block_link').hide();
      }
    });
  };

  const externalLinks = () => {
    $(document).on('click', '.external-link', e => {
      window.open($(e.currentTarget).attr('href'));
      return false;
    });
  };

  const showMap = containerId => {
    const mapElement = document.getElementById(containerId);
    const lat = mapElement.getAttribute('data-lat');
    const lng = mapElement.getAttribute('data-lng');

    mapboxgl.accessToken = config.mapboxApiKey;

    // Map
    const mapOptions = {
      center: [lng, lat],
      zoom: 15,
      container: containerId,
      style: 'mapbox://styles/mapbox/streets-v10',
    };
    const map = new mapboxgl.Map(mapOptions);
    map.addControl(new mapboxgl.NavigationControl());

    // Marker
    const el = document.createElement('div');
    el.className = 'marker';
    el.style.backgroundImage = 'url(/svg/marker.svg)';
    el.style.backgroundSize = 'cover';
    el.style.width = '40px';
    el.style.height = '40px';

    new mapboxgl.Marker(el).setLngLat([lng, lat]).addTo(map);
  };

  const makeSidebar = options => {
    // Fix containers
    const containers = `${options.container} .container`;
    $(options.container).addClass('container  sidebar__container');
    $(containers)
      .removeClass('container  container--thinner')
      .addClass('container--with-sidebar');

    // Handle small screens
    $(options.toggle).on('click', () => {
      $(options.hideable).toggleClass('sidebar-hidden-small');
      $(options.overlay).toggleClass('sidebar__darkened-overlay');
    });
  };

  const carousel = () => {
    $('.carousel-sidenav li').on('click', e => {
      e.preventDefault();
      $('.carousel-sidenav li').each((index, el) => {
        $(el).removeClass('active');
      });
      $(e.currentTarget).addClass('active');
    });
  };

  const skipLinks = () => {
    $('.js-skip-links a').on('focus', () => {
      $('.js-skip-links').addClass('active');
      $('body').css('margin-top', $('.js-skip-links').height());
    });
    $('.js-skip-links a').on('blur', () => {
      $('.js-skip-links').removeClass('active');
      $('body').css('margin-top', '0');
    });
  };

  return {
    equalheight,
    resized,
    pieChart,
    checkButton,
    video,
    externalLinks,
    showMap,
    makeSidebar,
    carousel,
    customModal,
    skipLinks,
  };
})(jQuery);

export default App;
