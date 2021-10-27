require(
    [
        'jquery',
        'Magento_Checkout/js/action/get-totals',
    ],
    function ($, getTotalsAction) {
        'use strict';

        let binCampaign = window.checkoutConfig.binCampaign;
        if (binCampaign && binCampaign.enabled && binCampaign.ids) {

            let inputs = binCampaign.ids.split(',');
            let inputsValidated = [];

            // Observer to detect the creation of inputs to listen
            let inputObserver = new MutationObserver(function(mutations) {
                $($('#payment').find('input')).each(function() {
                    // Validates if the input is in the list of fields to validate
                    if (inputs.indexOf(this.id) !== -1) {
                        // Validates if the input is already in the list of validated entries
                        if (inputsValidated.indexOf(this.id) === -1) {
                            inputsValidated.push(this.id);
                            this.addEventListener('keyup', (event) => {
                                let binCode = this.value.substring(0, 6);
                                if (binCode.length === 6) {
                                    $.ajax({
                                        url: '/checkout/bines/setbincode',
                                        data: { bin_code: binCode },
                                    }).done(function(data) {
                                        getTotalsAction([]);
                                    });
                                }
                            });
                        }
                    }
                });
                // Turn off the observer when the listener is added to each input
                if (inputsValidated.length === inputs.length) {
                    inputObserver.disconnect();
                }
            });
            inputObserver.observe(document.body, {childList:true, subtree:true});

        }
    }
);
