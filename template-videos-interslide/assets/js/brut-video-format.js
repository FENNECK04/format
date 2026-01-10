document.addEventListener('click', (event) => {
  const target = event.target;
  if (target.classList.contains('brut-video-format__copy')) {
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
