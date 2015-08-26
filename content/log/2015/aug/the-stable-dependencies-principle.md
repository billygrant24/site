---
title: Building Better Software: The Stable Dependencies Principle
author: William
template: log
---

This article is the first in a series I intend to write on the principles of software development.

Most developers will be familiar with the <abbr title="Don't Repeat Yourself">DRY</abbr> (Don't Repeat Yourself) 
principle. It states that:

> Every piece of knowledge must have a single, unambiguous, authoritative representation within a system.

I complement <abbr title="Don't Repeat Yourself">DRY</abbr> with another, less formal principle - 
<abbr title="Don't Repeat Your Peers">DRYP</abbr> (Don't Repeat Your Peers). Roughly, 
<abbr title="Don't Repeat Your Peers">DRYP</abbr> states:

> Every component in system must have a single, unambiguous, authoritative responsibility.

It seems to make sense for me. However, while it is plain to see the benefit of integrating third-party libraries over 
green-field code, the developer must be conscious of introducing instability to a system. That is, the developer 
should understand that every new dependency in a system further reduces overall stability of its host.

For disambiguation, we use stable in this context to describe how easy (and by extension, how likely) it is
to modify a piece of software. A stable package, all dependencies accounted for, is one that should be resistant to 
change. In contrast, an unstable package is volatile, that is, likely to change. By acknowledging this, we have a
stark truth - a package can only ever be as stable as it's most unstable dependency.

Fortunately we can attain peace of mind and prove the stability of a package with the following equation:

> Where `Ca` is Afferent Couplings (the number of classes external to this package that depend on classes within this
> package) and `Ce` is Efferent Couplings (the number of classes within this package that depend upon classes outside of
> the package)
>
> `I = (Ce / (Ca+Ce))`
>
> <footer>Courtesy of <cite title="Source Title">Robert C. Martin</cite></footer>

Following this, when `I = 0`, your package has reached maximum stability. When `I = 1`, your package has reached 
maximum instability.

Clearly we cannot require that all dependencies are stable, nor would it be desirable. Indeed it would be a sign of 
poor design were all of the packages in a system of similar stability.

We should demand however that the stability of a package correlate with its position in our dependency graph. The 
lower down the graph, the more unstable we can permit a package to be.

Concluding with a real-world example, a package which has a single defined purpose and no dependencies, such as 
`Psr\Log`, is one that we should expect to be highly stable. It provides an interface and little more. Whenever we
implement `Psr\Log` we have peace of mind that the api is unlikely to change.

Always be sure that you are building on ground which is both SOLID and stable.