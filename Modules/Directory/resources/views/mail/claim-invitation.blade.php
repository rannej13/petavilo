<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f6f4f1;font-family:Arial,Helvetica,sans-serif;color:#1f1f1f;font-size:15px;line-height:1.55">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;padding:28px">
<tr><td>
<p style="margin:0 0 16px;font-weight:bold;font-size:18px">{{ $site }}</p>
<p>Hello {{ $business->getAttribute('name') }} team,</p>
@if($isFollowUp)
<p>A quick reminder: your free listing on {{ $site }} is still waiting for you to claim it.</p>
@else
<p>{{ $site }} is a directory and marketplace just for pet owners in the UK and US. <strong>{{ $business->getAttribute('name') }}</strong> already has a free {{ $typeName }} listing@if($business->getAttribute('town')) in {{ $business->getAttribute('town') }}@endif, built from public business information.</p>
@endif
<p><a href="{{ $listingUrl }}" style="color:#b24200">See your listing</a></p>
<p>Claiming it is free and takes two minutes. Once claimed you can:</p>
<ul style="padding-left:20px">
<li>correct your phone, website and address</li>
<li>add opening hours, services, prices and photos</li>
<li>show your accreditations</li>
<li>get the Verified badge</li>
<li>see how many pet owners viewed your page and clicked through</li>
</ul>
@if(($trialMonths ?? 0) > 0)
<p style="background:#fff4e5;padding:12px 14px;border-radius:8px"><strong>We're choosing a few local businesses for a free trial:</strong> claim your listing with the button below and you get {{ $trialMonths }} months of Premium free. That's top placement in your town, your photos, an offer for new clients, and a monthly count of the views, website visits and calls we send you. No card needed, no commission on your bookings, ever. After the trial it simply goes back to the free listing unless you choose a plan.</p>
<p style="margin:24px 0"><a href="{{ $claimUrl }}" style="background:#e85d04;color:#ffffff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold">Claim and start {{ $trialMonths }} months free</a></p>
@else
<p style="margin:24px 0"><a href="{{ $claimUrl }}" style="background:#e85d04;color:#ffffff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold">Claim your free listing</a></p>
<p style="color:#555">Optional paid upgrades put you at the top of your town's results and on our homepage, but nothing is required.</p>
@endif
<p>Thanks,<br>{{ $senderName !== '' ? $senderName.', ' : '' }}{{ $site }}</p>
<hr style="border:none;border-top:1px solid #eee;margin:24px 0">
<p style="font-size:12px;color:#777">You received this {{ $isFollowUp ? 'reminder' : 'one-off email' }} because {{ $business->getAttribute('name') }} is listed in our public directory. We will send at most one reminder. <a href="{{ $unsubscribeUrl }}" style="color:#777">Stop all emails from us</a>. To have your listing removed, reply to this email.</p>
@if($companyLine !== '')<p style="font-size:12px;color:#777">{{ $companyLine }}</p>@endif
</td></tr></table>
</td></tr></table>
</body>
</html>
