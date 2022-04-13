const playBtns = document.querySelectorAll('.btn-play')

if (playBtns) {
    for (const playBtn of playBtns) {
        playBtn.addEventListener('click', event => {
            event.preventDefault()
            const albumId = playBtn.dataset.album

            fetch('/album/ecouter/' + albumId, {
                'headers': {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(response => {
                    playBtn.parentNode.querySelector('.nbplays').innerHTML = response.nbPlays
                    playBtn.parentNode.querySelector('.lastlistened').innerHTML = '&nbsp;~&nbsp;' + response.lastListened

                })
                .catch(error => alert("Erreur : " + error));
        })
    }
}
