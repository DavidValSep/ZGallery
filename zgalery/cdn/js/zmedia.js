// Copia adaptada de ZtartCMS/public/js/zmedia.js
(function () {
  if (window.__zmediaInitialized) {
    return;
  }
  window.__zmediaInitialized = true;

  let zboxOverlay = null;
  let zboxImage = null;

  function ensureZboxOverlay() {
    if (zboxOverlay) {
      return;
    }
    zboxOverlay = document.createElement('div');
    zboxOverlay.className = 'zbox-overlay';

    zboxImage = document.createElement('img');
    zboxImage.alt = '';
    zboxOverlay.appendChild(zboxImage);

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'zbox-close-btn';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', closeZbox);
    zboxOverlay.appendChild(closeBtn);

    zboxOverlay.addEventListener('click', (event) => {
      if (event.target === zboxOverlay) {
        closeZbox();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeZbox();
      }
    });

    document.body.appendChild(zboxOverlay);
  }

  function openZbox(url, caption) {
    ensureZboxOverlay();
    if (!zboxOverlay || !zboxImage) {
      return;
    }
    zboxImage.src = url;
    zboxImage.setAttribute('title', caption || '');
    requestAnimationFrame(() => {
      zboxOverlay.classList.add('is-open');
    });
  }

  function closeZbox() {
    if (zboxOverlay) {
      zboxOverlay.classList.remove('is-open');
      if (zboxImage) {
        zboxImage.src = '';
      }
    }
  }

  function setupZbox() {
    if (!document.body) {
      return;
    }

    document.body.addEventListener('click', (event) => {
      const trigger = event.target.closest('[data-zbox]');
      if (!trigger) {
        return;
      }
      event.preventDefault();
      const href = trigger.getAttribute('href') || trigger.dataset.src;
      if (!href) {
        return;
      }
      const caption = trigger.getAttribute('data-caption') || trigger.getAttribute('title') || '';
      openZbox(href, caption);
    });

    document.body.addEventListener('mouseover', (event) => {
      const trigger = event.target.closest('[data-zbox]');
      if (!trigger) {
        return;
      }
      const caption = trigger.getAttribute('data-caption');
      if (caption && !trigger.getAttribute('title')) {
        trigger.setAttribute('title', caption);
      }
    });
  }

  function setupZplayer() {
    const wrappers = document.querySelectorAll('[data-zplayer]');
    wrappers.forEach((wrapper) => {
      if (wrapper.dataset.enhanced === '1') {
        return;
      }
      wrapper.dataset.enhanced = '1';
      wrapper.classList.add('zplayer');
      const video = wrapper.querySelector('video');
      const audio = wrapper.querySelector('audio');

      if (video) {
        if (window.videojs) {
          window.videojs(video, {
            controls: true,
            preload: 'metadata'
          });
        } else {
          video.controls = true;
        }
      }

      if (audio) {
        audio.controls = true;
        wrapper.classList.add('zplayer-audio');
      }
    });
  }

  function initZmedia() {
    setupZbox();
    setupZplayer();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initZmedia);
  } else {
    initZmedia();
  }

  window.zmediaRefresh = initZmedia;
})();
