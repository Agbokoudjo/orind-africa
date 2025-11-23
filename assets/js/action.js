import {
    handleToggleConfirmation,
    MissingAttributeError,
    showLoadingDialog,
    HttpResponse,
    showErrorDialog,
    showSuccessDialog,
    httpFetchHandler,
    mapStatusToResponseType
 } from "@wlindabla/form_validator";
import { EVENT_ACCOUNT_TOGGLE_CONFIRMED } from "./event.js";

/**
 * Gère le clic sur le bouton 'toggle account' et affiche la modale de confirmation.
 */
export function toggleAccountHandle(subject) {
    jQuery(subject).on('click', 'a.btn-toggle-account', async (event) => {
        event.preventDefault();
         await handleToggleConfirmation({
            element: event.currentTarget,
             eventName: EVENT_ACCOUNT_TOGGLE_CONFIRMED,
             confirmDialogConfig: {
                icon: 'warning',
                background: '#00427E',
                color: '#fff',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: await SonataTranslator.trans('LABEL_BTN_CONFIRM','sonata-translations'),
                cancelButtonText: await SonataTranslator.trans('LABEL_BTN_CANCEL','sonata-translations'),
                showClass: {
                    popup: 'animate__animated animate__bounceIn'
                }
            },
             cancelDialogConfig: {
                icon: 'info',
                title: await SonataTranslator.trans('ACTION_CANCELLED_SUCCESS','sonata-translations'),
                timer: 3000
            }
        });
    });
};

/**
 * Gère l'action de renvoi d'email de vérification via SweetAlert2.
 * * Ce script est conçu pour écouter les clics sur les boutons ayant l'attribut 
 * data-action-confirm, ce qui déclenche la modale de confirmation.
 */
export function resendEmailVerification() {
    jQuery(document).on('click', 'a.btn-resend-email-verification', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        const { title, actionConfirmText, actionUrl } = extractResendEmailVerificationData(e.currentTarget);
        const resultSwal=await Swal.fire({
                title: title,
                html: `<div class="alert alert-warning" role="alert">${actionConfirmText}</div>` ,
                icon: 'question',
                position:"top",
                showCancelButton: true,
                 background: "#00427E",
                color:"#fff",
                confirmButtonText: await SonataTranslator.trans('LABEL_BTN_CONFIRM','sonata-translations'),
                cancelButtonText: await SonataTranslator.trans('LABEL_BTN_CANCEL','sonata-translations'),
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary me-2'
                },
            didOpen: () => {
                const container = document.querySelector<HTMLElement>('.swal2-container');
                if (container) {
                    container.style.zIndex = '99999';
                }
            },
            showClass: {
                popup: "animate__animated animate__fadeInUp animate__faster"
            },
            hideClass: {
                popup: "animate__animated animate__fadeOutDown animate__faster"
            },
        })

        if (resultSwal.dismiss) { 
            await Swal.fire({
                icon: "info",
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                showCloseButton: true,
                title:await SonataTranslator.trans('ACTION_CANCELLED_SUCCESS','sonata-translations'),
            });
            return;
        }

        /**
         * @type NodeJS.Timeout | undefined
         */
        let timerInterval;
        
        try {
             
            Swal.close();
            const timer =showLoadingDialog({
                config:{
                     background: "#00427E",
                     color:"#fff",
                     title: await SonataTranslator.trans('FORM_SUBMISSION_PROGRESS_TITLE','sonata-translations'),
                     text: await SonataTranslator.trans('ACTION_PENDING_RESEND_EMAIL_VERIFCATION_MESSAGE','sonata-translations'),
                }
            })

            timerInterval = timer.timerInterval;
            const response = await httpFetchHandler({
                url: actionUrl,
                methodSend: 'GET',
                retryCount:2,
                responseType:"json"
            });

            //Close loading dialog
            Swal.close();
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = undefined;
            }

            //Check if response indicates error
            if (mapStatusToResponseType(response.status) === "error") {
                throw response;
            }
            const responseData = response.data || response;

            //Show success dialog
            await showSuccessDialog({
                title: responseData.title,
                 message: responseData.message,
                 config:{
                    background: "#00427E",
                    color:"#fff"
                }
            });

        } catch (error) {
            //Close loading dialog on error
            Swal.close();

            if (timerInterval) {
                clearInterval(timerInterval);
            }
            // 11. Log error
            console.error("❌ RESEND EMAIL VERIFCATION action failed:", error);
            // 12. Determine error message and title
            let errorMessage = "An error occurred";
            let errorTitle = "Error";
            if (error instanceof HttpResponse) {
                // HTTP response error
                console.error(`HTTP ${error.status}:`, error.data);
                const errorData = error.data ;
                errorMessage = errorData.message || errorMessage;
                errorTitle =`HTTP ${error.status}`;
            } else if (error instanceof Error) {
                errorMessage = fetchErrorTranslator.translate(error.name, error, document.documentElement.lang);
                errorTitle = errorTitle || error.name;
            }

            // 13. Show error dialog
            showErrorDialog({
                title: errorTitle,
                message: errorMessage,
                 config:{
                    background: "#00427E",
                    color:"#fff",
                 }
            });

        }
    })
}

/**
 * @fileoverview Gestionnaire d'événements pour la régénération de mot de passe temporaire via AJAX/PATCH.
 * Nécessite jQuery et le composant de gestion de flash messages de votre application (si utilisé).
 */
export function handlePasswordRegeneration() {
    // 1. Écouteur d'événement sur la classe spécifique du bouton
    jQuery(document).on('click', '.js-regenerate-password-btn', async function(event) {
        event.preventDefault();
        const { title, actionConfirmText, actionUrl } = extractResendEmailVerificationData(event.currentTarget);

        const resultSwal=await Swal.fire({
                title: title,
                html: `<div class="alert alert-warning" role="alert">${actionConfirmText}</div>` ,
                icon: 'question',
                position:"top",
                showCancelButton: true,
                 background: "#00427E",
                color:"#fff",
                confirmButtonText: await SonataTranslator.trans('LABEL_BTN_CONFIRM','sonata-translations'),
                cancelButtonText: await SonataTranslator.trans('LABEL_BTN_CANCEL','sonata-translations'),
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary me-2'
                },
            didOpen: () => {
                const container = document.querySelector<HTMLElement>('.swal2-container');
                if (container) {
                    container.style.zIndex = '99999';
                }
            },
            showClass: {
                popup: "animate__animated animate__fadeInUp animate__faster"
            },
            hideClass: {
                popup: "animate__animated animate__fadeOutDown animate__faster"
            },
        })

        if (resultSwal.dismiss) { 
            await Swal.fire({
                icon: "info",
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                showCloseButton: true,
                title:await SonataTranslator.trans('ACTION_CANCELLED_SUCCESS','sonata-translations'),
            });
            return;
        }

        /**
         * @type NodeJS.Timeout | undefined
         */
        let timerInterval;
        
        try {
             
            Swal.close();
            const timer =showLoadingDialog({
                config:{
                     background: "#00427E",
                     color:"#fff",
                     title: await SonataTranslator.trans('FORM_SUBMISSION_PROGRESS_TITLE','sonata-translations'),
                     text: await SonataTranslator.trans('FORM_SUBMISSION_PROGRESS_MESSAGE','sonata-translations'),
                }
            })

            timerInterval = timer.timerInterval;
            const response = await httpFetchHandler({
                url: actionUrl,
                methodSend: 'PATCH',
                retryCount:2,
                responseType:"json"
            });

            //Close loading dialog
            Swal.close();
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = undefined;
            }

            //Check if response indicates error
            if (mapStatusToResponseType(response.status) === "error") {
                throw response;
            }
            const responseData = response.data || response;

            //Show success dialog
            await showSuccessDialog({
                title: responseData.title,
                 message: responseData.message,
                 config:{
                    background: "#00427E",
                    color:"#fff"
                }
            });

        } catch (error) {
            //Close loading dialog on error
            Swal.close();

            if (timerInterval) {
                clearInterval(timerInterval);
            }
            // 11. Log error
            console.error("❌ RESEND EMAIL VERIFCATION action failed:", error);
            // 12. Determine error message and title
            let errorMessage = "An error occurred";
            let errorTitle = "Error";
            if (error instanceof HttpResponse) {
                // HTTP response error
                console.error(`HTTP ${error.status}:`, error.data);
                const errorData = error.data ;
                errorMessage = errorData.message || errorMessage;
                errorTitle =`HTTP ${error.status}`;
            } else if (error instanceof Error) {
                errorMessage = fetchErrorTranslator.translate(error.name, error, document.documentElement.lang);
                errorTitle = errorTitle || error.name;
            }

            // 13. Show error dialog
            showErrorDialog({
                title: errorTitle,
                message: errorMessage,
                 config:{
                    background: "#00427E",
                    color:"#fff",
                 }
            });

        }
    });
}

/**
 * Extracts and validates toggle data from a DOM element
 * 
 * Uses jQuery internally for maximum browser compatibility (IE8+).
 * Validates all required attributes and parses additional data.
 * 
 * @param element - DOM element containing toggle data attributes
 * @returns Validated toggle data object
 * @throws {TypeError} If element is null or undefined
 * @throws {Error} If jQuery is not available
 * @throws {MissingAttributeError} If required attributes are missing
 * 
 * @example
 * ```typescript
 * const element = document.querySelector('.btn-resend-email-verification');
 * const data = extractToggleData(element);
 * console.log(data.title); // "Toggle Account"
 * ```
 */
export function extractResendEmailVerificationData(element) {
    // Convert to jQuery for compatibility
    const $element = jQuery(element);

    // Extract and validate data-action-confirm
    const actionConfirmText = $element.attr("data-action-confirm");
    if (!actionConfirmText || actionConfirmText.trim() === "") {
        throw new MissingAttributeError("data-action-confirm", element.outerHTML);
    }

    // Extract title (Bootstrap 5 compatible)
    const title =
        $element.attr("title") ||
        $element.attr("data-bs-original-title") ||
        $element.attr("data-original-title");

    if (!title || title.trim() === "") {
        throw new MissingAttributeError("title", element.outerHTML);
    }

    // Extract action URL
    const actionUrl =
        $element.attr("href") ||
        $element.attr("data-href") ||
        $element.attr("data-url");

    if (!actionUrl || actionUrl.trim() === "") {
        throw new MissingAttributeError("href or data-url", element.outerHTML);
    }

     return {
        title,
        actionConfirmText,
        actionUrl
    };
}

