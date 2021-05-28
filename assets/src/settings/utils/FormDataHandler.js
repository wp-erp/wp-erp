/**
 * Generate Form Data from Object
 *
 * @since 1.8.4
 *
 * @param object object data
 *
 * @return Object FormData Object
 */
export const generateFormDataFromObject = ( object ) => {
    let formData = new FormData();
    buildFormData( formData, object );
    return formData;
}

const buildFormData = ( formData, data, parentKey ) => {
    if ( data && typeof data === 'object' && ! ( data instanceof Date ) && !( data instanceof File )) {
        Object.keys( data ).forEach( key => {
            buildFormData( formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
        });
    } else {
        const value = data == null ? '' : data;
        formData.append( parentKey, value );
    }
}
