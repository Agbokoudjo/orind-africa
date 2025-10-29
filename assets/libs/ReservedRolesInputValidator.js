/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

import { AbstractFieldValidator } from '@wlindabla/form_validator';

/**
 * @typedef {Object} ReservedRolesConfig
 * @property {string[]} reservedRoles Liste des rôles réservés (ex: ["ROLE_FOUNDER"]).
 */

/**
 * Objet de configuration par défaut pour la validation des rôles réservés.
 * @type {ReservedRolesConfig}
 */
export const ReservedRolesDefaults = {
    reservedRoles: [],
};

/**
 * Validateur de rôle réservé.
 * Étend la classe de base FormError pour la gestion des messages et de l'état.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
export class ReservedRolesInputValidator extends AbstractFieldValidator {

    /** @type {ReservedRolesInputValidator} */
    static #instance = null; 
    
    constructor() {
        super();
    }

    /**
     * Obtient l'instance unique du validateur (Singleton).
     * @returns {ReservedRolesInputValidator}
     */
    static getInstance() {
        if (!ReservedRolesInputValidator.#instance) { // Utilisez #instance pour accéder à la propriété privée
           ReservedRolesInputValidator.#instance = new ReservedRolesInputValidator();
        }
        return ReservedRolesInputValidator.#instance;
    }

    /**
     * Vérifie si le rôle saisi fait partie des rôles réservés.
     * * @param {string} value_input La valeur saisie par l'utilisateur (le nom du rôle).
     * @param {string} targetInputname Le nom du champ ciblé.
     * @param {ReservedRolesConfig} options Options contenant la liste des rôles réservés.
     * @returns {ReservedRolesInputValidator} Retourne l'instance (this) en cas de succès, ou le résultat de setValidatorStatus en cas d'erreur.
     */
    validate(value_input, targetInputname, options) {
        this.formErrorStore.clearFieldState(targetInputname);
        const { reservedRoles } = options;
        const roleName = value_input.toUpperCase(); 

        if (reservedRoles.includes(roleName)) { 

            return this.setValidationState(
                false,
                `Le nom de rôle "${roleName}" est réservé et ne peut pas être créé.`,
                targetInputname
            );
        }
        
        return this;
    }
}

export const reservedRolesValidator = ReservedRolesInputValidator.getInstance();
