export default function(fn, delay) {
    let timeoutID = null;
    return function() {
        clearTimeout(timeoutID);
        const args = arguments;
        const that = this;
        timeoutID = setTimeout(() => {
            fn.apply(that, args);
        }, delay);
    };
}
