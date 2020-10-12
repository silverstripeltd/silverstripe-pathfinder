require('nodelist-foreach-polyfill');
require('element-closest-polyfill');

const RadioNestedSubsetField = () => {
  const singles = document.querySelectorAll('[data-role="parent-input"]');
  const multis = document.querySelectorAll('[data-role="multi-choice"] input[type="checkbox"]');

  // When a single radio is changed, we want to unselect any checked choices
  singles.forEach((element) => {
    element.addEventListener('change', (e) => {
      const radio = e.currentTarget;

      multis.forEach((checkbox) => {
        const parent = checkbox.closest('[data-role="multi-choice"]').querySelector('[data-role="parent-input"]');

        if (radio !== parent) {
          // Ensure the choice is checked
          // eslint-disable-next-line no-param-reassign
          checkbox.checked = false;
        }
      });
    });
  });

  // When a checkbox is changed, we want to uncheck any radios that aren't its parent
  multis.forEach((element) => {
    element.addEventListener('change', (e) => {
      const checkbox = e.currentTarget;
      const radio = checkbox.closest('[data-role="nesting-parent"]').querySelector('[data-role="parent-input"]');

      if (!radio.checked) {
        // Ensure the parent radio field is checked
        radio.checked = true;
      }
    });
  });
};

window.addEventListener('load', () => {
  RadioNestedSubsetField();
});

