import { Controller } from "@hotwired/stimulus";

import { httpFetchHandler, addParamToUrl } from "@wlindabla/form_validator"

export default class extends Controller{
    
    static targets = ["userTypeSelect", "userSelect"];

    connect() {
         // on vérifie que les cibles existent
        if (!this.hasUserTypeSelectTarget || !this.hasUserSelectTarget) return;

        this.userTypeSelectTarget.addEventListener("change", async () => {
            await this.loadUsers(this.userTypeSelectTarget.value);
        });
        
    }
    
    /**
     * 
     * @param {String} selectedType ;
     * @return
     */
    async loadUsers(selectedType) {
        try {
            
            const data_users = await httpFetchHandler({
                url: addParamToUrl(window.location.href, { userType: selectedType }),
                methodSend: "GET",
                timeout: 3600,
                retryOnStatusCode: true,
                responseType: "json"
            });

            // On vide la liste puis on la remplit
            const select = this.userSelectTarget;
            select.innerHTML = "";
            data_users.data.forEach(user => {
                const opt = document.createElement("option");
                opt.value = user.id;
                opt.textContent = user.username;
                select.appendChild(opt);
            });
        } catch (error) {
            console.error("Erreur chargement utilisateurs :", error);
        }
    }
}