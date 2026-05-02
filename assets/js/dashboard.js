// Real-time timer update
function updateTimers() {
	const timers = document.querySelectorAll('.real-time-timer');
	timers.forEach((timer) => {
		const startTime = new Date(timer.getAttribute('data-start-time'));
		const now = new Date();
		const diff = now - startTime;

		const hours = Math.floor(diff / (1000 * 60 * 60));
		const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
		const seconds = Math.floor((diff % (1000 * 60)) / 1000);

		timer.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
	});
}

// Update timers every second
setInterval(updateTimers, 1000);

// Initial update
updateTimers();

// Auto-refresh page every 30 seconds to get new data
// setInterval(() => {
//    location.reload();
//}, 30000);

// Form focus effects
document.querySelectorAll('.form-control, .form-select').forEach((element) => {
	element.addEventListener('focus', function () {
		this.parentElement.classList.add('focused');
	});

	element.addEventListener('blur', function () {
		this.parentElement.classList.remove('focused');
	});
});
