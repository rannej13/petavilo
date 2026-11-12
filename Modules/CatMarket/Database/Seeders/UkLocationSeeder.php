<?php

declare(strict_types=1);

namespace Modules\CatMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Location\Models\City;
use Modules\Location\Models\Country;

class UkLocationSeeder extends Seeder
{
    public function run(): void
    {
        $uk = Country::query()->updateOrCreate(
            ['code' => 'GB'],
            ['name' => 'United Kingdom', 'phone_code' => '+44', 'is_active' => true],
        );

        $us = Country::query()->updateOrCreate(
            ['code' => 'US'],
            ['name' => 'United States', 'phone_code' => '+1', 'is_active' => true],
        );

        Country::query()->whereNotIn('id', [$uk->getKey(), $us->getKey()])->update(['is_active' => false]);

        foreach ([[$uk, $this->cities()], [$us, $this->usCities()]] as [$country, $cities]) {
            foreach ($cities as $city) {
                City::query()->updateOrCreate(
                    ['country_id' => (int) $country->getKey(), 'name' => $city],
                    ['is_active' => true],
                );
            }
        }
    }

    private function usCities(): array
    {
        return [
            'Albuquerque, NM', 'Anchorage, AK', 'Atlanta, GA', 'Austin, TX', 'Baltimore, MD', 'Birmingham, AL',
            'Boise, ID', 'Boston, MA', 'Buffalo, NY', 'Burlington, VT', 'Charleston, SC', 'Charlotte, NC',
            'Cheyenne, WY', 'Chicago, IL', 'Cincinnati, OH', 'Cleveland, OH', 'Columbus, OH', 'Dallas, TX',
            'Denver, CO', 'Des Moines, IA', 'Detroit, MI', 'El Paso, TX', 'Fargo, ND', 'Fort Worth, TX',
            'Hartford, CT', 'Honolulu, HI', 'Houston, TX', 'Indianapolis, IN', 'Jackson, MS', 'Jacksonville, FL',
            'Kansas City, MO', 'Las Vegas, NV', 'Little Rock, AR', 'Los Angeles, CA', 'Louisville, KY',
            'Manchester, NH', 'Memphis, TN', 'Miami, FL', 'Milwaukee, WI', 'Minneapolis, MN', 'Nashville, TN',
            'New Orleans, LA', 'New York, NY', 'Newark, NJ', 'Oklahoma City, OK', 'Omaha, NE', 'Orlando, FL',
            'Philadelphia, PA', 'Phoenix, AZ', 'Pittsburgh, PA', 'Portland, ME', 'Portland, OR', 'Providence, RI',
            'Raleigh, NC', 'Richmond, VA', 'Sacramento, CA', 'Salt Lake City, UT', 'San Antonio, TX',
            'San Diego, CA', 'San Francisco, CA', 'San Jose, CA', 'Seattle, WA', 'Sioux Falls, SD', 'Spokane, WA',
            'St. Louis, MO', 'Tampa, FL', 'Tucson, AZ', 'Virginia Beach, VA', 'Washington, DC', 'Wichita, KS',
            'Wilmington, DE', 'Charleston, WV', 'Billings, MT',
        ];
    }

    private function cities(): array
    {
        return [
            'Aberdeen', 'Bath', 'Belfast', 'Birmingham', 'Blackburn', 'Blackpool', 'Bolton', 'Bournemouth',
            'Bradford', 'Brighton', 'Bristol', 'Cambridge', 'Canterbury', 'Cardiff', 'Carlisle', 'Chelmsford',
            'Cheltenham', 'Chester', 'Colchester', 'Coventry', 'Crawley', 'Derby', 'Doncaster', 'Dundee',
            'Durham', 'Edinburgh', 'Exeter', 'Glasgow', 'Gloucester', 'Huddersfield', 'Hull', 'Inverness',
            'Ipswich', 'Lancaster', 'Leeds', 'Leicester', 'Lincoln', 'Liverpool', 'London', 'Luton',
            'Manchester', 'Middlesbrough', 'Milton Keynes', 'Newcastle upon Tyne', 'Newport', 'Northampton',
            'Norwich', 'Nottingham', 'Oxford', 'Perth', 'Peterborough', 'Plymouth', 'Portsmouth', 'Preston',
            'Reading', 'Sheffield', 'Shrewsbury', 'Southampton', 'Southend-on-Sea', 'Stirling', 'Stockport',
            'Stoke-on-Trent', 'Sunderland', 'Swansea', 'Swindon', 'Telford', 'Truro', 'Wakefield', 'Warrington',
            'Wigan', 'Winchester', 'Wolverhampton', 'Worcester', 'Wrexham', 'York', 'Derry', 'Lisburn', 'Newry',
        ];
    }
}
