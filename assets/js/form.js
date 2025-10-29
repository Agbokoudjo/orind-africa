import {
    FormValidateController,
    addHashToIds,
    FieldValidationFailed,
    addErrorMessageFieldDom,
     clearErrorInput
} from "@wlindabla/form_validator"

export function formValidate() {
    const form_exist = document.querySelector('form.form-validator');
    if (form_exist ===null) {
        return;
    }
    
    const form_validate = new FormValidateController('.form-validate');
    const __form = form_validate.form;

    const idsBlur = addHashToIds(form_validate.idChildrenUsingEventBlur).join(",");
    const idsInput = addHashToIds(form_validate.idChildrenUsingEventInput).join(",");
    const idsChange = addHashToIds(form_validate.idChildrenUsingEventChange).join(",");

    __form.on("blur", `${idsBlur}`, async (event) => {
        const target = event.target;
        if ((target instanceof HTMLInputElement ||
            target instanceof HTMLTextAreaElement)
           && target.type !== "file") {

            await form_validate.validateChildrenForm(target);
        }
    });

    __form.on(FieldValidationFailed, (event) => {
        const data = (event.originalEvent).detail;

        addErrorMessageFieldDom(jQuery(data.targetChildrenForm), data.message,'container-div-error-message');
    });

    __form.on('input', `${idsInput}`, (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {

            clearErrorInput(jQuery(target));
        }
    });
}