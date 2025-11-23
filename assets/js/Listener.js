import {processToggleAction,Logger} from "@wlindabla/form_validator";
import { EVENT_ACCOUNT_TOGGLE_CONFIRMED } from "./event.js";

export function toggleUserAccountListener() {
    jQuery(document).on(EVENT_ACCOUNT_TOGGLE_CONFIRMED, async function toggleAccount(event) {
        const detail = event.detail;
    
        Swal.close();
        try {
            const response = await processToggleAction({
                eventDetail: event.detail,
                httpMethod: 'PATCH',
                retryCount: 2,
                loadingConfig: {
                    background: '#00427E',
                    color: '#fff',
                    title: await SonataTranslator.trans('ACTION_PENDING_TITLE', 'sonata-translations'),
                    text: await SonataTranslator.trans('ACTION_PENDING_MESSAGE', 'sonata-translations')
                },
                translator: (key, error, lang) => {
                    if (error) {
                        return fetchErrorTranslator.translate(error.name, error, lang);
                    }
                    return translations[key] || key; 
                },
            });
            Logger.log(response)
        } catch (error) {
            Logger.log(error);
        }
    })
}

