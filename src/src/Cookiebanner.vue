<template>
    <div :class="['ccc-banner', positionClass]" :style="{ backgroundColor: bannerBackgroundColor }">
        <h3 class="ccc-banner__title">{{ title }}</h3>
        <p class="ccc-banner__description">{{ text }}</p>

        <form method="POST">
            <input type="hidden" :name="csrfName" :value="csrfValue" />
            <input type="hidden" name="action" value="complete-cookie-consent/consent/submit" />
            <label :for="'cookieType-' + type.handle" v-for="type in cookieTypes" :key="type.handle">
                <input v-if="type.required" type="hidden" name="cookieTypes[]" :value="type.handle" value="1" />
                <input :id="'cookieType-' + type.handle" type="checkbox" name="cookieTypes[]" :value="type.handle" :checked="type.defaultOn" :disabled="type.required == 1" /> {{ type.name }}
            </label>

            <input type="submit" :value="buttonText" :style="{ backgroundColor: buttonColor, color: buttonTextColor }" class="ccc-banner__button" />
        </form>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                title: cccSettings.bannerTitle,
                text: cccSettings.bannerText,
                buttonColor: cccSettings.bannerButtonColor,
                buttonTextColor: cccSettings.bannerButtonTextColor,
                buttonText: cccSettings.bannerButtonText,
                positionClass: 'ccc-banner--' + cccSettings.bannerPosition,
                bannerBackgroundColor: cccSettings.bannerColor,
                cookieTypes: cccSettings.cookieTypes,
                csrfName: csrfTokenName,
                csrfValue: csrfTokenValue,
            };
        }
    }
</script>
