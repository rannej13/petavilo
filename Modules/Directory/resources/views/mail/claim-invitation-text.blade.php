Hello {{ $business->getAttribute('name') }} team,

@if($isFollowUp)
A quick reminder from {{ $site }}. Your free listing is still waiting for you to claim it.
@else
{{ $site }} is a directory and marketplace just for pet owners in the UK and US. {{ $business->getAttribute('name') }} already has a free {{ $typeName }} listing@if($business->getAttribute('town')) in {{ $business->getAttribute('town') }}@endif, built from public business information:
@endif

{{ $listingUrl }}

Claiming it is free and takes two minutes. Once claimed you can:
- correct your phone, website and address
- add opening hours, services, prices and photos
- show your accreditations
- get the Verified badge
- see how many pet owners viewed your page and clicked through

@if(($trialMonths ?? 0) > 0)
We're choosing a few local businesses for a free trial: claim your listing and you get {{ $trialMonths }} months of Premium free. Top placement in your town, your photos, an offer for new clients, and a monthly count of the views, website visits and calls we send you. No card needed, no commission on your bookings, ever. After the trial it goes back to the free listing unless you choose a plan.

Claim and start your free trial: {{ $claimUrl }}
@else
Claim it here: {{ $claimUrl }}

Optional paid upgrades put you at the top of your town's results and on our homepage, but nothing is required.
@endif

Thanks,
{{ $senderName !== '' ? $senderName.', ' : '' }}{{ $site }}

You received this {{ $isFollowUp ? 'reminder' : 'one-off email' }} because {{ $business->getAttribute('name') }} is listed in our public directory. We will send at most one reminder. To stop all emails from us: {{ $unsubscribeUrl }}
To have your listing removed, reply to this email.
{{ $companyLine }}
