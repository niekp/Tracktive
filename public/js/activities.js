document.querySelectorAll('[data-favorite]').forEach(function (btn) {
	btn.addEventListener('click', function (e) {

		if (e.target.dataset.favorite == 1) {
			e.target.style.color = 'gray';
			e.target.dataset.favorite = 0;
		} else {
			e.target.style.color = 'gold';
			e.target.dataset.favorite = 1;
		}

		fetch("/activities/" + e.target.dataset.favoriteId + "/favorite", {
			method: "POST",
			body: JSON.stringify({
				favorite: e.target.dataset.favorite == 1,
			}),
			headers: {
				"Content-type": "application/json; charset=UTF-8",
				'X-CSRF-Token': document.querySelector('meta[name="_token"]').content,
			}
		}).catch(function () {
			alert('Er ging iets mis.');
		});
	});
})
