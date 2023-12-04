// Script for handling the typing effect on input fields
const inputs = {
    username: {
      element: document.getElementById('username'),
      phrases: ["Gadisa"]
    },
    password: {
      element: document.getElementById('password'),
      phrases: ["*********"]
    }
  };
  
  function type(inputKey) {
    const input = inputs[inputKey];
    const phrases = input.phrases;
    let phraseIndex = 0;
    let letterIndex = 0;
    let currentPhrase = [];
    let isDeleting = false;
  
    (function typePhrase() {
      const phrase = phrases[phraseIndex];
      if (!isDeleting) {
        currentPhrase.push(phrase[letterIndex++]);
      } else {
        currentPhrase.pop();
        letterIndex--;
      }
  
      if (!isDeleting && letterIndex === phrase.length) {
        setTimeout(() => isDeleting = true, 2000);
      }
  
      if (isDeleting && letterIndex === 0) {
        isDeleting = false;
        phraseIndex = (phraseIndex + 1) % phrases.length;
      }
  
      input.element.placeholder = currentPhrase.join('');
      const typingSpeed = isDeleting ? 100 : 200;
      setTimeout(typePhrase, typingSpeed);
    })();
  }
  
  // Initialize the typing effect once the window loads
  window.onload = function() {
    type('username');
    type('password');
  };
  
  // Script for handling cursor-following text effect
  // Script for handling cursor-following typing and untyping effect
const typingText = document.getElementById('typing-text');
let typingCompleted = false;

document.addEventListener('mousemove', function(e) {
  if (!typingCompleted) {
    typingText.style.left = `${e.pageX + 15}px`; // Offset text from the cursor
    typingText.style.top = `${e.pageY}px`;
    typingText.style.display = 'inline';

    if (!typingText.classList.contains('typing')) {
      typingText.classList.add('typing'); // Add 'typing' class to start the animation
    }
  }
});

typingText.addEventListener('animationend', (e) => {
  if (e.animationName === 'typing') {
    setTimeout(() => {
      typingText.classList.remove('typing');
      typingText.classList.add('untyping'); // Switch to 'untyping' after typing completes
    }, 5000); // Wait for 5 seconds before starting the untyping
  }

  if (e.animationName === 'untyping') {
    typingText.style.display = 'none';
    typingCompleted = true; // Once untyping is done, prevent re-typing
  }
});

  
  // Script for toggling password visibility
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const svgEyeClosed = togglePassword.querySelectorAll('svg')[0]; // Eye closed SVG
  const svgEyeOpen = togglePassword.querySelectorAll('svg')[1]; // Eye open SVG
  
  togglePassword.addEventListener('click', function (e) {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
  
    // Toggle SVG icon visibility for password field
    svgEyeClosed.style.display = (svgEyeClosed.style.display === 'none') ? 'block' : 'none';
    svgEyeOpen.style.display = (svgEyeOpen.style.display === 'block') ? 'none' : 'block';
  });
  
  // Include GSAP animations for form labels
  gsap.to("#label_user", { duration: 10, x: 200 });
  gsap.to("#label_pass", { duration: 6, x: 50 });
  