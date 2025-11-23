/**
 * Retrieves the content of the 'content' attribute of a <meta> tag by its name.
 * This function relies on jQuery being available globally.
 *
 * @param {string} name The name of the <meta> tag (e.g., "sonata-translations").
 * @returns {string} The value of the 'content' attribute.
 * @throws {Error} If the <meta> tag does not exist or if the 'content' attribute is empty.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
import * as basicLightbox from '../libs/basicLightbox.js';

/**
 * afficher une image avec basicLightbox 
 */ 
//Pour les images Adapter Jquery
export function basicLightboxImage() {
    jQuery(document).on('click', '.ea-lightbox-thumbnail-image', (event) => {
        const lightboxContent = jQuery(event.currentTarget).attr('src'); 
        const imgbalise = `<img src="${lightboxContent}" class="img-fluid">`
        const optionsimg = { className: 'custom-lightbox' }
        renderLightbox(imgbalise, optionsimg);
    })
}

export function basicLightboxDocument(){
     jQuery(document).on('click', '.ea-lightbox-thumbnail-document', (event) => {
        event.preventDefault();
         const currentTarget = jQuery(event.currentTarget);
        const document_path = currentTarget.attr('src') ?? currentTarget.attr('href') ?? currentTarget.attr('original-url') ; 
         const documentIframe = `<iframe src="${document_path}" width="100%" height="100%" frameborder="0"></iframe>`;
        renderLightbox(documentIframe, {
                className: 'custom-lightbox-document', 
                width: '100%', 
                height: '100%'
            });
    })
}

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
export function renderLightbox(documentbalise, optionsbasicLightbox) {
    const lightbox = basicLightbox.create(documentbalise, optionsbasicLightbox);
    lightbox.show();
}
