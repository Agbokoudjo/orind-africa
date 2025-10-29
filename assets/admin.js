import './bootstrap.js';

import Swal from 'sweetalert2'
import {  addErrorMessageFieldDom, clearErrorInput } from '@wlindabla/form_validator';
import { reservedRolesValidator } from './libs/ReservedRolesInputValidator.js';
import { formValidate } from "./js/form.js";
window.Swal = Swal;

jQuery(function init() {
    formValidate();
    reservedRoleValidate();
})

function reservedRoleValidate() {
    const inputReservedRoleValidate = jQuery('[data-input-reserved-roles-validate="true"]');
    
    if (inputReservedRoleValidate.length > 0) {
        
        // ------------------------------------------------------------------
        // GESTION DE L'ÉVÉNEMENT 'blur' (Validation)
        // ------------------------------------------------------------------
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


