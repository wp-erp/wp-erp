export const clickOutside = {
    bind(el, binding, vnode) {
        const bubble = binding.modifiers.bubble;
        const handler = e => {
            if (bubble || (!el.contains(e.target) && el !== e.target)) {
                binding.value(e);
            }
        };

        el.__vueClickOutside__ = handler;
        document.addEventListener('click', handler);
    },

    unbind(el, binding) {
        document.removeEventListener('click', el.__vueClickOutside__);
        el.__vueClickOutside__ = null;
    }
};
