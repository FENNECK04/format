document.addEventListener('click', (event) => {
  const target = event.target;
  if (target.classList.contains('isv-embed__play')) {
    const wrapper = target.closest('.isv-embed--lazy');
    if (!wrapper) {
      return;
    }
    const url = wrapper.getAttribute('data-url');
    const provider = wrapper.getAttribute('data-provider');
    if (!url) {
      return;
    }
    let embedUrl = url;
    if (provider === 'youtube' && url.indexOf('embed') === -1) {
      const videoId = new URL(url).searchParams.get('v') || url.split('/').pop();
      embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
    }
    if (provider === 'vimeo' && url.indexOf('player.vimeo.com') === -1) {
      const videoId = url.split('/').pop();
      embedUrl = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
    }
    const iframe = document.createElement('iframe');
    iframe.setAttribute('src', embedUrl);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
    iframe.setAttribute('allowfullscreen', '');
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    wrapper.innerHTML = '';
    wrapper.appendChild(iframe);
  }

  if (target.classList.contains('isv-copy')) {
    const url = target.getAttribute('data-url');
    if (!url) {
      return;
    }
    navigator.clipboard.writeText(url).then(() => {
      target.textContent = 'Lien copié';
      setTimeout(() => {
        target.textContent = 'Copier le lien';
      }, 2000);
    });
  }
});
