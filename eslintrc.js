module.exports = {
    extends: ['plugin:vue/essential', 'standard'],
    rules  : {
        semi                         : ['error', 'always'],
        indent                       : ['error', 4],
        'no-multi-spaces'            : ['off'],
        'key-spacing'                : ['off'],
        'space-before-function-paren': ['error', 'never'],
        camelcase                    : ['off']
    }
};
