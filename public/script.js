(function () {
	let branchesSelect = document.getElementById('branches-select');
	branchesSelect.onchange = function (ev) {
		location.href = '/' + ev.target.value;
	};
})();
