<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blog;
use Illuminate\Support\Arr;

/**
 * Blog posts rendered by the public /blog listing and detail pages.
 * Bodies are Markdown — the frontend renders them with raw HTML stripped.
 */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->all();

        foreach ($this->posts() as $post) {
            Blog::updateOrCreate(
                ['slug' => $post['slug']],
                $post + ['user_id' => $userIds ? Arr::random($userIds) : null]
            );
        }
    }

    private function posts(): array
    {
        // Demo art comes from demo_image(); see the helper — it is removed before submission.
        $img = fn ($id) => demo_image($id, 'wide');

        return [
            [
                'slug'         => 'maldives-travel-tips',
                'title'        => '10 things to know before visiting the Maldives',
                'author'       => 'Sami Chowdhury',
                'category'     => 'Beach',
                'image'        => $img('1573843981267-be1999ff37cd'),
                'excerpt'      => 'From seaplane transfers to the best time to visit — plan the perfect Maldives escape.',
                'read_minutes' => 6,
                'status'       => 'published',
                'published_at' => '2026-04-28',
                'body'         => <<<'MD'
The Maldives is more than a postcard. A little planning turns a good trip into a great one.

## When to go

The dry season runs from November to April, with the clearest water and the least rain. Prices peak between December and February, so late April often gives the best balance of weather and value.

## Getting to your island

Almost every resort sits on its own island. You will transfer from Malé by speedboat, domestic flight or seaplane — seaplanes only operate in daylight, so an evening arrival usually means an overnight stay in Malé.

## Practical tips

- Book half-board or full-board: island restaurants are expensive.
- Bring reef-safe sunscreen; many resorts ban the other kind.
- Alcohol is only served on resort islands, not local ones.
- Pack light, dry-fast clothing — humidity is high year round.
- Snorkelling gear is often free at the resort, so leave yours at home.

## Let us plan it

We arrange the flights, the transfer and the resort as a single package, so nothing falls between the cracks. [Talk to us](/contact-us) and we will build it around your dates.
MD,
            ],
            [
                'slug'         => 'schengen-visa-checklist',
                'title'        => 'Your complete Schengen visa checklist',
                'author'       => 'Nabila Karim',
                'category'     => 'Visa',
                'image'        => $img('1488085061387-422e29b40080'),
                'excerpt'      => 'Everything you need to prepare for a smooth, successful Schengen application.',
                'read_minutes' => 7,
                'status'       => 'published',
                'published_at' => '2026-04-15',
                'body'         => <<<'MD'
A Schengen refusal is almost always a paperwork problem, not a travel problem. Here is what a complete file looks like.

## Core documents

- Passport valid for at least 3 months beyond your return date, with two blank pages.
- Completed and signed application form.
- Two recent photographs to Schengen specification.
- Travel medical insurance covering at least €30,000.
- Confirmed round-trip flight reservation.
- Confirmed accommodation for every night of the trip.

## Proving your finances

Six months of bank statements, your salary certificate or trade licence, and tax returns. The bank balance should comfortably cover your daily costs — plan for roughly €60 per day.

## Proving you will return

This is where most applications fall down. Employment letters, property papers, family ties and a clear day-by-day itinerary all help.

## Apply early

Lodge your application four to six weeks before departure. Appointment slots disappear fast in peak season.

We prepare and check the whole file before submission — [start your application](/book/visa) and we will send you a tailored checklist.
MD,
            ],
            [
                'slug'         => 'first-umrah-guide',
                'title'        => 'A first-timer’s guide to a peaceful Umrah',
                'author'       => 'Nabila Karim',
                'category'     => 'Faith',
                'image'        => $img('1591604129939-f1efa4d9f7fa'),
                'excerpt'      => 'Practical advice for performing your first Umrah with calm and confidence.',
                'read_minutes' => 5,
                'status'       => 'published',
                'published_at' => '2026-03-30',
                'body'         => <<<'MD'
Your first Umrah is a spiritual journey, but it is also a logistical one. Preparing well leaves you free to focus on the worship.

## Before you travel

Learn the rites in order — ihram, tawaf, sa'i, and the trim or shave. Watching a short walkthrough the night before helps far more than reading alone.

## What to pack

- Two sets of ihram, plus a belt with a pocket.
- Unscented soap and toiletries.
- Comfortable sandals that are easy to slip off.
- A small foldable bag for the Haram.
- Your own prayer mat and a refillable bottle.

## In Makkah

Go to the Haram outside peak hours if you tire easily — after Fajr and late at night are calmest. Keep your hotel card with you; the streets look very similar at night.

## Choosing a package

Distance to the Haram matters more than star rating. We publish the walking distance for every hotel in our [Umrah packages](/umrah) so you can judge for yourself.
MD,
            ],
            [
                'slug'         => 'dubai-on-a-budget',
                'title'        => 'How to do Dubai on a budget',
                'author'       => 'Rifat Ahmed',
                'category'     => 'City',
                'image'        => $img('1512453979798-5ea266f8880c'),
                'excerpt'      => 'Luxury city, smart spending — our favourite ways to save in Dubai.',
                'read_minutes' => 5,
                'status'       => 'published',
                'published_at' => '2026-03-18',
                'body'         => <<<'MD'
Dubai has a reputation for excess, but it rewards travelers who plan.

## Travel in the shoulder season

October and April give you pleasant weather without peak-season hotel rates. July and August are cheapest of all if you can handle the heat and stay indoors mid-day.

## Move like a local

- Buy a Nol card and use the Metro — it reaches most attractions.
- The abra across Dubai Creek costs a single dirham.
- Public beaches such as Kite Beach are free and well kept.

## Eat well for less

Skip the mall food courts and eat where the residents eat: Al Karama and Deira serve excellent food at a fraction of Marina prices.

## Free things worth doing

The Dubai Fountain show, Al Fahidi historical district, Alserkal Avenue galleries and the Miracle Garden in season.

Ready to go? See our [tour packages](/tour-packages) for Dubai.
MD,
            ],
            [
                'slug'         => 'bali-itinerary',
                'title'        => 'The perfect 6-day Bali itinerary',
                'author'       => 'Lima Akter',
                'category'     => 'Adventure',
                'image'        => $img('1537996194471-e657df975ab4'),
                'excerpt'      => 'Beaches, temples and rice terraces — see the best of Bali in under a week.',
                'read_minutes' => 6,
                'status'       => 'published',
                'published_at' => '2026-03-02',
                'body'         => <<<'MD'
Six days is enough for Bali if you resist the urge to see everything.

## Days 1–2: Seminyak

Arrive, recover, and ease in with beach clubs and sunset dinners. Good for jet lag, and a soft landing before the busier days.

## Days 3–4: Ubud

Move inland. Tegallalang rice terraces at sunrise, the Sacred Monkey Forest, a Balinese cooking class, and a waterfall or two. Ubud is also where the best spas are.

## Day 5: Temples and the east

Tirta Empul, Besakih or Lempuyang depending on your appetite for stairs. Dress respectfully — a sarong is required and usually provided.

## Day 6: Uluwatu

Clifftop temple, the Kecak fire dance at sunset, then a seafood dinner on Jimbaran beach before your flight.

## Getting around

Hire a driver for full days rather than booking rides piecemeal — it costs less and saves hours.
MD,
            ],
            [
                'slug'         => 'cheap-flights-tips',
                'title'        => '7 proven ways to find cheaper flights',
                'author'       => 'Sami Chowdhury',
                'category'     => 'Tips',
                'image'        => $img('1436491865332-7a61a109cc05'),
                'excerpt'      => 'Booking windows, flexible dates and the tricks that actually work.',
                'read_minutes' => 4,
                'status'       => 'published',
                'published_at' => '2026-02-20',
                'body'         => <<<'MD'
Most "flight hacks" are noise. These seven consistently work.

1. **Book in the window.** Six to ten weeks ahead for short-haul, three to five months for long-haul.
2. **Be flexible by a day.** Tuesday and Wednesday departures are routinely cheaper than Friday.
3. **Check nearby airports.** A short transfer can save a meaningful amount on the fare.
4. **Consider a self-connect.** Two separate tickets sometimes beat one through fare — just leave a wide buffer.
5. **Watch the baggage rules.** A cheap fare plus paid bags is often more expensive overall.
6. **Set fare alerts early.** Prices move; alerts mean you do not have to.
7. **Ask a human.** Consolidator fares are not always published online.

That last one is what we do all day. [Send us your route](/book/flight) and we will quote the real best fare.
MD,
            ],
            [
                'slug'         => 'hajj-2026-preparation-guide',
                'title'        => 'Hajj preparation guide',
                'author'       => 'Nabila Karim',
                'category'     => 'Faith',
                'image'        => $img('1591604129939-f1efa4d9f7fa'),
                'excerpt'      => 'A month-by-month plan so nothing is left to the last week.',
                'read_minutes' => 8,
                'status'       => 'draft',
                'published_at' => null,
                'body'         => null,
            ],

            // ---- Earlier demo posts, backfilled with real content so the
            // ---- public listing has no half-empty cards. ----
            [
                'slug'         => '10-underrated-destinations',
                'title'        => '10 underrated destinations worth the detour',
                'author'       => 'Lima Akter',
                'category'     => 'Adventure',
                'image'        => $img('1506744038136-46273834b3fb'),
                'excerpt'      => 'Skip the queues — these places deliver more for less.',
                'read_minutes' => 5,
                'status'       => 'published',
                'published_at' => '2026-05-28',
                'body'         => <<<'MD'
Everyone books the same ten cities. These are the ones our consultants quietly recommend instead.

## Why go off the list

Fewer crowds, lower prices, and hosts who still have time for you. In most cases the flights cost the same — it is the ground costs that drop.

## Our picks

- **Tbilisi, Georgia** — mountains, wine and a visa policy that favours Bangladeshi travelers.
- **Da Nang, Vietnam** — beach, old town and mountain passes within an hour of each other.
- **Pokhara, Nepal** — Annapurna views without a trek.
- **Muscat, Oman** — the Gulf at half the price of its neighbours.
- **Almaty, Kazakhstan** — genuine four-season travel and short flights.
- **Colombo & Ella, Sri Lanka** — the train ride alone justifies the trip.
- **Penang, Malaysia** — the best street food in Southeast Asia.
- **Baku, Azerbaijan** — old city, new architecture, easy e-visa.
- **Chiang Mai, Thailand** — slower and cheaper than Bangkok.
- **Bandarban, Bangladesh** — hills and rivers, no passport needed.

Tell us the vibe you want and we will match a destination to it.
MD,
            ],
            [
                'slug'         => 'best-time-to-visit-maldives',
                'title'        => 'The best time to visit the Maldives',
                'author'       => 'Sami Chowdhury',
                'category'     => 'Beach',
                'image'        => $img('1514282401047-d79a71a590e8'),
                'excerpt'      => 'Season by season — when to go for weather, for diving, or for value.',
                'read_minutes' => 4,
                'status'       => 'published',
                'published_at' => '2026-05-20',
                'body'         => <<<'MD'
There is no bad time to visit, but there is a right time for what you want.

## For weather: December to March

Dry, sunny and calm. It is also peak season, so book three to four months out.

## For value: May to July

The wet season brings short afternoon showers rather than all-day rain, and resort rates fall sharply.

## For diving: August to November

Plankton blooms draw manta rays and whale sharks to the atolls, especially around Baa.

## For a honeymoon

Late April hits the sweet spot: dry-season weather, shoulder-season pricing.

See our current [Maldives packages](/tour-packages?q=Maldives).
MD,
            ],
            [
                'slug'         => 'budget-travel-tips-for-asia',
                'title'        => 'Budget travel tips for Asia',
                'author'       => 'Lima Akter',
                'category'     => 'Tips',
                'image'        => $img('1502602898657-3e91760cbb34'),
                'excerpt'      => 'Stretch your budget without cutting the parts of the trip that matter.',
                'read_minutes' => 5,
                'status'       => 'published',
                'published_at' => '2026-05-12',
                'body'         => <<<'MD'
Asia rewards travelers who spend selectively rather than uniformly.

## Spend where it counts

Pay for a good bed and good transport. Save on everything else — street food is usually better than the restaurant version anyway.

## Move overland where it makes sense

Night trains and long-distance buses save you a hotel night as well as the fare. In Vietnam, Thailand and Sri Lanka they are genuinely comfortable.

## Time your bookings

- Book internal flights the moment your dates are fixed.
- Book accommodation late in low season, early in high season.
- Avoid Chinese New Year and local holiday weeks unless that is the point of the trip.

## Carry the right money

Cards are widely accepted in cities and useless in villages. Keep small local notes on you and withdraw larger amounts less often to reduce ATM fees.
MD,
            ],
            [
                'slug'         => 'how-to-choose-travel-insurance',
                'title'        => 'How to choose travel insurance',
                'author'       => 'Nabila Karim',
                'category'     => 'Tips',
                'image'        => $img('1488646953014-85cb44e25828'),
                'excerpt'      => 'What the policy actually needs to cover — and what you can safely skip.',
                'read_minutes' => 4,
                'status'       => 'published',
                'published_at' => '2026-05-05',
                'body'         => <<<'MD'
Most travelers buy insurance to satisfy a visa officer. Buy it properly and it earns its cost the one time you need it.

## Non-negotiables

- Medical cover of at least €30,000 (mandatory for Schengen).
- Emergency evacuation and repatriation.
- Cover for the entire trip duration, including transit days.
- 24-hour assistance line that works from abroad.

## Worth adding

Trip cancellation and baggage delay, if you have paid a large non-refundable deposit or are connecting through a busy hub.

## Usually skippable

Gadget cover you already have on a home policy, and rental-car excess if your card provides it.

## Read the exclusions

Adventure activities, pre-existing conditions and travel against official advice are the three that catch people out most often.
MD,
            ],
            [
                'slug'         => 'dubai-layover-itinerary',
                'title'        => 'What to do on a Dubai layover',
                'author'       => 'Rifat Ahmed',
                'category'     => 'City',
                'image'        => $img('1512453979798-5ea266f8880c'),
                'excerpt'      => 'Six hours, twelve hours, or a full day — three plans that actually fit.',
                'read_minutes' => 4,
                'status'       => 'published',
                'published_at' => '2026-04-28',
                'body'         => <<<'MD'
A Dubai connection is long enough to be a trip in itself, if you plan around immigration time.

## Under 6 hours: stay airside

Terminal 3 has a rest zone, showers and a quiet lounge. Leaving is possible but tight — you will spend most of it in transit.

## 6 to 10 hours: the Metro plan

Clear immigration, take the Red Line to Burj Khalifa/Dubai Mall, see the fountain show, and be back with two hours to spare. Bring your Nol card, not cash.

## 12 hours or more: old and new

Add Al Fahidi historical district and the abra across the Creek in the morning, then the Marina in the evening. A half-day driver costs less than three taxis.

## Practical notes

- Most Bangladeshi passport holders need a transit or tourist visa to leave the airport — we can arrange it.
- Left-luggage counters are in every terminal.
- Summer afternoons are brutal; plan indoor stops between noon and 4pm.
MD,
            ],
        ];
    }
}
