require([
    'jquery',
], function ($) {
    const xlmreviews_stars = "data:image/png;base64,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";
    const xlmreviews_base_url = 'https://xml.ssreviewsportal.com/api/';
    var xlmreviews = (function () {
        return {
            initialized: false,
            xlmreviews_sku: () => {
                let flixmedia = $(document).find('script[data-flix-sku]').data('flix-sku');
                let xlmreviews = $(document).find('.xlmreviews');
                if (flixmedia && xlmreviews) {
                    return flixmedia;
                } else {
                    return false;
                }
            },
            xlmreviews_reviews: [],
            general: '',
            templates: {
                xlmreviews_t1: (r, w) => {
                    return `
                    <small class="xlmreviews__cta">Ver opiniones de consumidores</small>
                    <figure class="xlmreviews__figure">
                        <div class="xlmreviews__before" style="width:${w}%"></div>
                        <img src="${xlmreviews_stars}" class="xlmreviews__img">
                    </figure>
                    <div class="xlmreviews__rating">${parseFloat(r).toFixed(1)}(5)</div>
                `;
                },
                xlmreviews_t2: ({ LastPublishTime, Rating, ReviewText, Title, ReviewerNickname }) => {
                    let r = parseFloat(Rating).toFixed(1);
                    if (r >= 3) {
                        return `
                            <div class="xlmreviews__review">
                                <div class="xlmreviews__review__nickname">${ReviewerNickname}</div>
                                <div class="xlmreviews__review__rating__wrap">
                                    <div class="xlmreviews__review__rating">
                                        <figure class="xlmreviews__figure">
                                            <div class="xlmreviews__before" style="width:${((r * 100) / 5)}%"></div>
                                            <img src="${xlmreviews_stars}" class="xlmreviews__img">
                                        </figure>
                                        <div class="xlmreviews__rating">
                                            ${r}(5)
                                        </div>
                                    </div>
                                    <div class="xlmreviews__review__title">
                                        ${Title}
                                    </div>
                                </div>
                                <div class="xlmreviews__review__date">
                                    Publicado el ${new Date(LastPublishTime).toLocaleDateString('es-PA')}
                                </div>
                                <div class="xlmreviews__review__text">
                                    ${ReviewText}
                                </div>
                            </div>
                        `
                    }
                },
                xlmreviews_t3: (r) => {
                    return `
                        <div class="xlmreviews__btn_float">
                            <div class="xlmreviews__btn_float_cta">Ver opiniones de consumidores</div>
                            <div class="xlmreviews__btn_float_rating">
                                <div>
                                ${parseFloat(r).toFixed(1)}
                                </div>
                                <figure class="xlmreviews__btn_float_start">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576
                                        512"><path fill="#FFA41C" d="M381.2 150.3L524.9
                                            171.5C536.8 173.2 546.8 181.6 550.6 193.1C554.4
                                            204.7 551.3 217.3 542.7 225.9L438.5 328.1L463.1
                                            474.7C465.1 486.7 460.2 498.9 450.2 506C440.3 513.1
                                            427.2 514 416.5 508.3L288.1 439.8L159.8 508.3C149
                                            514 135.9 513.1 126 506C116.1 498.9 111.1 486.7
                                            113.2 474.7L137.8 328.1L33.58 225.9C24.97 217.3
                                            21.91 204.7 25.69 193.1C29.46 181.6 39.43 173.2
                                            51.42 171.5L195 150.3L259.4 17.97C264.7 6.954
                                            275.9-.0391 288.1-.0391C300.4-.0391 311.6 6.954
                                            316.9 17.97L381.2 150.3z" /></svg>
                                    </figure>
                                </div>
                            </div>
                        </div>
                    `;
                }
            },
            get_reviews: function () {
                $(".xlmreviews__modal").addClass("active");
                $(".xlmreviews__modal__content").addClass("active");
                if (this.xlmreviews_reviews.length == 0) {
                    let id = $('.xlmreviews').data('id')
                    if (typeof id == undefined || typeof id == 'undefined') {
                        return;
                    }
                    $('.xlmreviews__modal__content').html(`<div class="xlmreviews__load__wrap"><div class="xlmreviews__load"></div><div class="xlmreviews__load"></div><div class="xlmreviews__load"></div>`);
                    this.http({
                        body: new URLSearchParams({ id: id }),
                        url: xlmreviews_base_url + 'get-reviews/',
                        method: 'POST'
                    }).then(d => {
                        this.xlmreviews_reviews = JSON.parse(d.reviews)
                        if (this.xlmreviews_reviews.length > 0) {
                            $('.xlmreviews__modal__content').html(`
                                <div class="xlmreviews__modal__content__header">
                                    <h2>Opiniones de Consumidores</h2>
                                    <div class="xlmreviews__modal__content__header_name">
                                        <div class="xlmreviews">
                                            ${this.general.name} tiene un puntaje
                                            <figure class="xlmreviews__figure">
                                                <div class="xlmreviews__before" style="width:${((parseFloat(this.general.rating).toFixed(1) * 100) / 5)}%"></div>
                                                <img src="${xlmreviews_stars}" class="xlmreviews__img">
                                            </figure>
                                            <div class="xlmreviews__rating">${parseFloat(this.general.rating).toFixed(1)}</div>
                                            <div>
                                                con <b id="xlmreviews_reviews_length"> reseñas</b>
                                            </div>
                                    </div>
                                </div>
                            `);
                            $('.xlmreviews__modal__content').append(this.xlmreviews_reviews.map(this.templates.xlmreviews_t2).join(''));
                            $('.xlmreviews__modal__content').append(`
                            <div class="xlmreviews__modal__content__footer">
                                <a class="xlmreviews__modal__content__footer_a">Ver detalle del producto</a>
                            </div>
                            `);
                            $("#xlmreviews_reviews_length").html($('.xlmreviews__review').length+' Reseñas')
                        } else {
                            $('.xlmreviews__modal__content').html('<div style="text-align:center; margin-top:2rem">Este producto no tiene comentarios.<div>')
                        }
                    });
                }
            },
            init: function () {
                if (this.xlmreviews_sku()) {
                    this.initialized = true;
                    this.http({
                        body: new URLSearchParams({ sku: this.xlmreviews_sku() }),
                        url: xlmreviews_base_url + 'get-sku/',
                        method: 'POST'
                    }).then(d => {
                        $('head').append(`<style>.xlmreviews,.xlmreviews__review__rating{display:flex;flex-wrap:wrap;align-items:center;margin:0;padding:0;width:100%;min-height:40px;cursor:pointer}.xlmreviews__review__rating{width:auto;cursor:default;flex-wrap:wrap}.xlmreviews__review__rating__wrap{display:flex;flex-wrap:wrap;width:auto;align-items:center}.xlmreviews__cta{text-align:center;background-color:#000;color:#fff;padding:0.3rem;font-size:0.9rem;border-radius:0.5rem}.xlmreviews__figure{line-height:0;margin:0;padding:0;width:auto;min-height:30px;position:relative}.xlmreviews__before{content:"";display:block;position:absolute;left:0;right:0;width:0;height:100%;background-color:#ffa41c;z-index:0}.xlmreviews__img{margin:0;padding:0;height:30px;width:auto;position:relative;z-index:1}.xlmreviews__rating{font-size:1rem;padding:0;padding-right:0.5rem}.xlmreviews__modal{position:fixed;width:100%;height:100%;left:0;top:0;background-color:rgba(0,0,0,0.7);display:flex;justify-content:center;align-items:center;transform:scale(0,0);z-index:999999}.xlmreviews__modal.active{transition:all 0.3s ease-in-out;transform:scale(1,1)}.xlmreviews__modal__content__header{width:100%;text-align:center;position:sticky;top:0;background-color:#fff;padding:1rem;z-index:9999999999999}.xlmreviews__modal__content__header_name{padding-top:1rem}.xlmreviews__modal__content__header h2{font-size:2rem}@media screen and (max-width:480px){.xlmreviews__modal__content__header h2{font-size:1.7rem}}.xlmreviews__modal__content__header::after{content:"X";position:absolute;top:0.5rem;right:0.5rem;font-weight:bold;color:#000;font-size:1rem;border-radius:100%;padding:1.2rem;display:block;z-index:9999999999999999;cursor:pointer}.xlmreviews__modal__content{transform:translate(-50%,-50%) scale(0,0);position:fixed;left:50%;top:50%;margin:0 auto;box-shadow:0 0 5px #000;background-color:#fff;width:900px;max-width:90vw;height:90vh;border-radius:0.5rem;overflow-x:visible;overflow-y:auto;z-index:999999999999}.xlmreviews__modal__content.active{transform:translate(-50%,-50%) scale(1,1);transition:all 0.3s ease-in-out}.xlmreviews__modal__content__footer{position:sticky;bottom:0;text-align:center;background-color:#fff;padding:1rem;z-index:9999999999999}.xlmreviews__modal__content__footer_a{display:inline-block;text-align:center;background-color:#000;color:#fff;padding:0.3rem;font-size:1rem;border-radius:0.5rem}.xlmreviews__review{padding:1rem 1.5rem;border-bottom:1px solid #ccc}.xlmreviews__review:last-type{border:none}.xlmreviews__review__nickname{font-weight:bold;font-size:1.2rem}.xlmreviews__review__title{font-weight:bold}.xlmreviews__review__date{font-size:0.75rem;color:#999;margin-bottom:0.5rem}.xlmreviews__review__text{font-size:0.9rem}.xlmreviews__load__wrap{position:absolute;width:100%;height:90%;left:0;right:0;display:flex;justify-content:center;align-items:center}.xlmreviews__load{width:0.875rem;height:5rem;background:#ccc;margin-left:0.875rem;animation:bar-loading 1s infinite alternate}.xlmreviews__load:nth-child(2){animation-delay:-0.2s}.xlmreviews__load:nth-child(3){animation-delay:-0.4s}@keyframes bar-loading{0%{transform:scaleY(0.5)}100%{transform:scaleY(1)}}.xlmreviews__btn_float{cursor:pointer;position:fixed;visibility:hidden;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;padding:0.5rem;border-radius:0.5rem;font-size:0.8rem;background-color:#000;color:#fff;box-shadow:0px 0 0.2rem #000}.xlmreviews__btn_float_cta{text-align:center;width:100px}.xlmreviews__btn_float_rating{margin-left:0.25rem;padding-left:0.25rem;display:flex;align-items:center;font-size:1.1rem;border-left:1px solid #fff;line-height:0}.xlmreviews__btn_float_start{padding-left:0.2rem;width:1.3rem}.xlmreviews__btn_float.xlmreviews_left .xlmreviews__btn_float_cta{order:2}.xlmreviews__btn_float.xlmreviews_left .xlmreviews__btn_float_rating{border-left:none;border-right:1px solid #fff;margin-left:0;padding-left:0;margin-right:0.25rem;padding-right:0.25rem;order:1}</style>`)
                        $('body').append(`<div class="xlmreviews__modal"></div><div class="xlmreviews__modal__content"><div class="xlmreviews__modal__content"></div></div>`);
                        $(document).on('click', '.xlmreviews__modal,.xlmreviews__modal__content__header,.xlmreviews__modal__content__footer_a', function (event) {
                            event.preventDefault();
                            $(".xlmreviews__modal").removeClass("active");
                            $(".xlmreviews__modal__content").removeClass("active");
                            $('body').css({ overflowY: "auto" });
                        });
                        $(document).on('click', '.xlmreviews,.xlmreviews__btn_float', function () {
                            xlmreviews.get_reviews()
                        });
                        if (typeof d.rating != 'undefined' && typeof d.rating != undefined && typeof d.id != 'undefined' && typeof d.id != undefined) {
                            this.general = d;
                            if (d.rating != 0 && d.id != 0) {
                                let container = $('.xlmreviews');
                                container.data('id', d.id)
                                let t = ((parseFloat(d.rating).toFixed(1) * 100) / 5)
                                container.html(this.templates.xlmreviews_t1(d.rating, t))
                                $('body').append(this.templates.xlmreviews_t3(d.rating));
                                let px = container.data('xlmreviewsPositionX')
                                let pxs = container.data('xlmreviewsPositionXSize')
                                let py = container.data('xlmreviewsPositionY')
                                let pys = container.data('xlmreviewsPositionYSize')
                                if (typeof px != undefined && typeof px != "undefined" && typeof pxs != undefined && typeof pxs != "undefined") {
                                    $('.xlmreviews__btn_float').css(px, pxs)
                                    if (px == 'left') {
                                        $('.xlmreviews__btn_float').addClass('xlmreviews_left')
                                    }
                                } else {
                                    $('.xlmreviews__btn_float').css('right', "1rem")
                                }
                                if (typeof py != undefined && typeof py != "undefined" && typeof pys != undefined && typeof pys != "undefined") {
                                    $('.xlmreviews__btn_float').css(py, pys)
                                } else {
                                    $('.xlmreviews__btn_float').css('bottom', "1rem")
                                }
                                $('.xlmreviews__btn_float').css('visibility', "visible")
                            }
                        }
                    });
                }
            },
            http: async (params) => {
                const settings = {
                    method: params.method,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    mode: 'cors',
                    cache: 'no-cache',
                    credentials: 'same-origin',
                    body: params.body
                };
                try {
                    const fetchResponse = await fetch(params.url, settings);
                    const data = await fetchResponse.json();
                    return data;
                } catch (e) {
                    return e;
                }
            }
        }
    })(xlmreviews || {});
    if (!xlmreviews.initialized) {
        xlmreviews.init();
    }
});
