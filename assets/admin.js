import './bootstrap.js';
import Swal from 'sweetalert2';
import {
    addErrorMessageFieldDom, 
    clearErrorInput,
    appTranslation,
    fetchErrorTranslator
 } from '@wlindabla/form_validator';
import { reservedRolesValidator } from './libs/ReservedRolesInputValidator.js';

import { 
    formValidate, 
   formformatterEventHandle
} from "./js/form.js";

import {
    toggleAccountHandle,
    resendEmailVerification,
    handlePasswordRegeneration
} from "./js/action.js";
import { toggleUserAccountListener } from "./js/Listener.js";
import { basicLightboxImage,basicLightboxDocument } from "./js/utils.js";
jQuery(async function init() {
    Admin.log('alert - JQuery est maintenant défini !');
    window.Swal = Swal;
    window.SonataTranslator = appTranslation;

     // Récupère le hash actuel des traductions
    const currentHash = jQuery('meta[name="sonata-translations-hash"]').attr('content');
    const cachedHash = localStorage.getItem('sonata_translations_hash');
    
    // Si le hash a changé, vide le cache
    if (currentHash && cachedHash !== currentHash) {
        Admin.log('Translation keys changed - clearing cache');
        await appTranslation.clearCache();
        localStorage.setItem('sonata_translations_hash', currentHash);
    }

    await appTranslation.preload('sonata-translations');
    window.fetchErrorTranslator =fetchErrorTranslator 
    formValidate();
    formformatterEventHandle();
    reservedRoleValidate();
    toggleAccountHandle(document);
    resendEmailVerification();
    handlePasswordRegeneration();
    toggleUserAccountListener();
    basicLightboxImage();
    basicLightboxDocument();
})

function reservedRoleValidate() {
    const inputReservedRoleValidate = jQuery('[data-input-reserved-roles-validate="true"]');
    
    if (inputReservedRoleValidate.length > 0) {
        
        inputReservedRoleValidate.on('blur', function (event) {
            const target = jQuery(event.target);
            const inputName = target.attr('name');
            
            try {
                
                const rolesJson = target.attr('data-reserved-roles') || '[]';
                const reservedRolesArray = JSON.parse(rolesJson);

                reservedRolesValidator.validate(
                    target.val(), // La valeur de l'input
                    inputName,
                    { reservedRoles: reservedRolesArray }
                );

                // Récupérer le statut après l'exécution du validateur
                const { isValid, errors } = reservedRolesValidator.getState(inputName);
                
                // Si validatorStatus est FALSE (échec de la validation), afficher l'erreur
                if (!isValid) {
                    addErrorMessageFieldDom(target, errors);
                }
                
            } catch (e) {
                console.error("Erreur de validation ou JSON invalide:", e);
                throw new Error(`Erreur de validation ou JSON invalide:${e}`)
            }
        });

        // ------------------------------------------------------------------
        // GESTION DE L'ÉVÉNEMENT 'input' (Effacement de l'erreur)
        // ------------------------------------------------------------------
        inputReservedRoleValidate.on('input', function (event) {
            const target = jQuery(event.target);
            clearErrorInput(target);
            reservedRolesValidator.formErrorStore.clearFieldState(target.attr('name'));
        });
    }
}


