# Part Numbering System

## Interdiction
Part numbers consist of a 1-3 character prefix, a six-digit integer, and an optional postfix.
Example:
* C-123456
* EM-654321#07

Part numbers are not case-sensitive.

## Postfix
Postfixes are used to add additional information to a part.
Example:
Let's say a piece of cable with a length of 350mm is needed. This can be specified as CBL-12345#350.

## Categories

Inv: Inventory (Equipment, Tools, ...)
Loc: Locations can be fixed in space or movable. Locations can contain other locations,  parts or inventory.
Stk: Stock Items.


C: Capacitor
D: Diode
F: Fuses
T: Transistor (Including Mosfets, IGBT, Triac, Tyristaors, ...)
L: Inductors (Including transformers)
LED: Light Emitting Diode
EM: Electromechanical Part
MEC: Mechanical Part
IC: Integrated Circuit
Frq: Frequency generator (aka. Quartz)

## API Overview
A REST-style API is used to create, modify or access data.

HTTP GET will get an item.
HTTP POST will create a new item.
HTTP PATCH will update an existing item.
