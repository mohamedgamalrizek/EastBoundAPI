# Partner airline logos

Drop each airline's logo here, named after the airline slug, and the home-page
"Trusted by leading airlines" strip picks it up automatically:

    Emirates          -> emirates.svg
    Qatar Airways     -> qatar-airways.svg
    Biman Bangladesh  -> biman-bangladesh.svg
    Malaysia Airlines -> malaysia-airlines.svg
    Singapore Airlines-> singapore-airlines.svg
    Turkish Airlines  -> turkish-airlines.svg
    Etihad Airways    -> etihad-airways.svg
    Saudia            -> saudia.svg

Accepted extensions, in priority order: svg, png, webp, jpg.
Recommended: SVG or a transparent PNG about 150x36.

The slug is Laravel's Str::slug() of the airline name stored on flight routes,
so any airline you add later works the same way. Anything without a file falls
back to a text lockup, so the strip never breaks.

No logos ship with the app: airline marks are trademarks and must be used under
the airline's own brand terms.
