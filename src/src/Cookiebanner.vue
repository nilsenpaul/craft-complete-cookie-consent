<template>
    <div v-if="consentInfo && bannerShouldBeShown && ((consentInfo.consentImplied && isFirstVisit) || (!consentInfo.consentImplied && consentInfo.consentNeeded && !consentInfo.consentSubmitted))" :class="['ccc-banner', 'ccc-banner--' + pluginSettings.bannerPosition]" :style="{ backgroundColor: pluginSettings.bannerColor }">
        <div class="ccc-banner__text">
            <h3 class="ccc-banner__title" v-html="pluginSettings.bannerTitle"></h3>
            <p class="ccc-banner__description" v-html="pluginSettings.bannerText"></p>
        </div>

        <form method="POST" class="ccc-banner__form ccc-form">
            <div class="ccc-form__inner">
                <input type="hidden" :name="csrfTokenName" :value="csrfTokenValue" />
                <input type="hidden" name="action" value="complete-cookie-consent/consent/submit" />
                <div class="ccc-banner__label-container">
                    <label :for="'cookieType-' + type.handle" v-for="type in pluginSettings.cookieTypes" :key="type.handle" class="ccc-form__label">
                        <input v-if="type.required" type="hidden" name="cookieTypes[]" :value="type.handle" value="1" />
                        <input :id="'cookieType-' + type.handle" type="checkbox" name="cookieTypes[]" :value="type.handle" :checked="type.defaultOn" :disabled="type.required == 1" class="ccc-form__input" />
                        <span class="ccc-form__label-text">{{ type.name }}</span>
                    </label>
                </div>

                <div class="ccc-banner__buttons">
                    <input type="submit" :value="pluginSettings.primaryButtonText" :style="{ backgroundColor: pluginSettings.primaryButtonBackgroundColor, color: pluginSettings.primaryButtonTextColor }" class="ccc-banner__button ccc-banner__button--primary" />
                    
                    <a :href="pluginSettings.secondaryButtonHref" v-if="pluginSettings.showSecondaryButton" :style="{ backgroundColor: pluginSettings.secondaryButtonBackgroundColor, color: pluginSettings.secondaryButtonTextColor   }" class="ccc-banner__button ccc-banner__button--secondary" v-html="pluginSettings.secondaryButtonText" :target="pluginSettings.secondaryButtonOpenInNewTab ? '_blank' : '_self'" rel="noopener nofollow">
                    </a>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                consentInfo: null,
                csrfTokenName: null,
                csrfTokenValue: null,
                pluginSettings: null,
                isFirstVisit: null,
                bannerShouldBeShown: null,
            };
        },
        mounted: function() {
            var that = this;
            this.$api
                .get(window.cccSiteUrl + '/actions/complete-cookie-consent/consent/banner-info')
                .then(function(response) {
                    // Fill a global variable for the website to use
                    window.ccc = response.data.consentInfo;

                    that.pluginSettings = response.data.pluginSettings; 
                    that.isFirstVisit = response.data.isFirstVisit; 
                    that.bannerShouldBeShown = response.data.bannerShouldBeShown; 
                    that.csrfTokenName = response.data.csrfTokenName; 
                    that.csrfTokenValue = response.data.csrfTokenValue; 
                    that.consentInfo = response.data.consentInfo; 

                    // Dispatch an event on the window element
                    var event = new Event('ccc.loaded');
                    window.dispatchEvent(event);
                });
        },
    }
</script>
