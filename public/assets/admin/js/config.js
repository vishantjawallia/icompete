
const JDLoader = {
    open: function () {
        const loaderContainer = document.getElementById('loader-container');
        if (loaderContainer) {
            loaderContainer.style.display = 'flex';
        }
    },
    close: function () {
        const loaderContainer = document.getElementById('loader-container');
        if (loaderContainer) {
            loaderContainer.style.display = 'none';
        }
    }
};
