<?php

namespace PooryaSadidi\ZarinPal;

use SocialiteProviders\Manager\SocialiteWasCalled;

class ZarinPalExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('zarinpal', Provider::class);
    }
}
