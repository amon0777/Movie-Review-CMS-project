function smoothScroll(event) {
    event.preventDefault();
    console.log("Smooth scrolling triggered"); // Check if this message appears in the console
    const target = document.querySelector(event.target.getAttribute('href'));
    window.scrollTo({
        top: target.offsetTop,
        behavior: 'smooth'
    });
    
    // Set a key in session storage to indicate that the smooth scroll transition has been triggered
    sessionStorage.setItem('smoothScrollTriggered', true);
    
    // Hide video container and welcome overlay
    const videoContainer = document.querySelector('.video-container');
    const welcomeOverlay = document.querySelector('.welcomeoverlay');
    if (videoContainer && welcomeOverlay) {
        videoContainer.style.display = 'none';
        welcomeOverlay.style.display = 'none';
    }
}

// Check session storage for the smooth scroll trigger on page load
window.addEventListener('DOMContentLoaded', function() {
    const smoothScrollTriggered = sessionStorage.getItem('smoothScrollTriggered');
    const videoContainer = document.querySelector('.video-container');
    const welcomeOverlay = document.querySelector('.welcomeoverlay');
    if (smoothScrollTriggered && videoContainer && welcomeOverlay) {
        videoContainer.style.display = 'none';
        welcomeOverlay.style.display = 'none';
    }
});
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');

    searchInput.addEventListener('input', function() {
      const query = searchInput.value.trim();
      if (query !== '') {
        fetchResults(query);
      } else {
        // Clear previous search results if query is empty
        clearResults();
      }
    });

    function fetchResults(query) {
      // Make an AJAX request to search.php with the query parameter
      // Example using fetch API
      fetch(`search.php?query=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(data => {
          // Display search results in a div with id 'searchResults'
          document.getElementById('searchResults').innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
    }

    function clearResults() {
      // Clear search results
      document.getElementById('searchResults').innerHTML = '';
    }
  });

