/**
 * Generate Form Data from Object
 *
 * @since 1.8.6
 *
 * @param object object data
 *
 * @return Object FormData Object
 */
export const generateFormDataFromObject = (object) => {
    let formData = new FormData();
    buildFormData(formData, object);
    return formData;
}

const buildFormData = (formData, data, parentKey) => {
    if (data && typeof data === 'object'
        && !(data instanceof Date)
        && !(data instanceof File)
    ) {
        Object.keys(data).forEach(key => {
            buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        });
    } else {
        let value = data == null ? '' : data;
        formData.append(parentKey, value);
    }
}

/**
 * Get Base 64 string from file
 *
 * @since 1.8.6
 *
 * @param object object data
 *
 * @return Promise
 */
const getBase64StringFromFile = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            let encoded = reader.result.toString().replace(/^data:(.*,)?/, '');
            if ((encoded.length % 4) > 0) {
                encoded += '='.repeat(4 - (encoded.length % 4));
            }
            resolve(encoded);
        };
        reader.onerror = error => reject(error);
    });
}
