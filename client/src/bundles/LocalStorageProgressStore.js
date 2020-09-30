require('nodelist-foreach-polyfill');

/**
 * @param {string} encoded
 */
const decodeData = (encoded) => JSON.parse(atob(decodeURIComponent(encoded)));

/**
 * The default component
 */
const LocalStorageProgressStore = () => {
  // This pattern uses an id and a timestamp to "sync" with the back end.
  // When the request provides an encoded copy of the progress data, we verify
  // to see if it's later than what is stored locally, before storing it.
  // If it's not the latest, we redirect to update the request to the latest store
  const encodedProgressField = document.querySelector('[name="progress"]');

  if (!encodedProgressField) {
    // Nothing to do
    return;
  }

  const encodedProgress = encodedProgressField.value;

  if (!encodedProgress) {
    // Nothing to do
    return;
  }

  const progress = decodeData(encodedProgress);

  if (typeof progress !== 'object') {
    // eslint-disable-next-line no-console
    console.warn('Unexpected value found in form\'s encoded store field');

    return;
  }

  const encodedLocal = window.localStorage.getItem('PathfinderProgress');

  if (encodedLocal) {
    const stored = decodeData(encodedLocal);

    if (stored.timestamp > progress.timestamp) {
      // Sigh. Let's swap out the ?progress param as safely as we can
      const loc = window.location;
      const queryParts = [];

      // Use substring to remove the leading '?'
      loc.search.substring(1).split('&').forEach((item) => {
        const param = item.split('=');

        if (param[0] === 'progress') {
          // Skip, so we can rewrite this param below
          return;
        }

        queryParts.push(item);
      });

      // Add our own progress param
      queryParts.push(`progress=${encodedLocal}`);

      // Redirect to the latest store
      window.location = [loc.origin, loc.pathname, '?', queryParts.join('&')].join('');
    }

    return;
  }

  // Sync down the latest store
  window.localStorage.setItem('PathfinderProgress', encodedProgress);
};

window.addEventListener('load', () => {
  // Instantiate component
  LocalStorageProgressStore();
});
