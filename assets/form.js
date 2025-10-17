import { httpFetchHandler, addParamToUrl } from "@wlindabla/form_validator"

jQuery(function userPermissionRoleForm() {
    const form_user_persmission_role= jQuery('form.user_permission_role');
    if (!form_user_persmission_role.length) { return; }

    const userTypeSelect = jQuery('[id$="_userType"]');
    const userSelect = jQuery('[id$="_userId"]');

    if (!userTypeSelect.length || !userSelect.length) { return; }

    userTypeSelect.on('change', async function (event) {
        let selectedType = jQuery(event.target).val();

        try {
            const data_users = await httpFetchHandler({
                url: addParamToUrl(window.location.href,{'userType':selectedType}),
                methodSend: "GET",
                timeout: 3600,
                retryOnStatusCode:true
            } );
        } catch (error) {
            
        }
    })
})