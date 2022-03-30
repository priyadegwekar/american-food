# Bookable Calendar

This aim to be a very easy to use Bookable Calendar module. Whether you're giving lessons and want
your students to be able to book a lesson or a business trying to stagger traffic into your building,
this module aims to get you up and running as fast as possible.

## Steps to create your first Calendar and take a Booking

### As an Admin

1. Install module as you would any module
2. Create your first Calendar (Under Content section of menu)
3. Create an Opening for that Calendar

### As an user (currently though admin interface UI coming soon)

1. Create a new Booking Contact filling out what Instance you want and party size.
2. On save it will tell you whether or not that will work based on max slots available and other things.

## Entities and what they do

### Bookable Calendar

This is the main Calendar that will be user facing. The thought would be if you're an individual giving
lessons it could be called "Piano Lessons" or if you're a company selling spots in line for Santa it
would say "Santa at the Mall" or whatever. It's fieldable so you can add any extra fields needed for
your situation by default it has the following fields:

- **Title:** The name of your Calendar
- **Active:** Whether or not you are currently allowing more bookings, this is so you can still show
the calendar but freeze Bookings.
- **Status:** Standard Drupal status, will hide this calendar from non admins.
- **Description:** This is a long text field that can be filled out to show any sort of body text
you want to show to users about this calendar.
- **Max Party Size:** Limit the max amount of bookings an individual contact can make to a single Opening.
- **Slots Per Opening:** How many bookings you want to allow per opening, if you're giving individual
lessons this might be 1 but if you're booking our an event space this might be 100 or whatever you need.
- **Booking Future Time:** This will limit user's ability to book openings more than X days/weeks/months in the future.
- **Booking Lead Time:** This is the minimum amount time in the future a Booking can take place. This can
be used to limit users booking same day/week/month booking
- **Calendar Openings:** This is not user created but is an entity reference to each opening for this Calendar.

### Bookable Calendar Opening

The individual openings for your Bookable Calendar. You can create as many of these as you'd like for
your calendar. This is where you define what dates and times are available to book. Due to it being
a multiple date value with repeat it's possible you could create only one of these per Bookable Calendar
but the option is there to create as many as needed. The advantage af splitting them out is then it's
easier to turn of a certain Opening whether that be a single day or opening as there is the status
field to quickly turn it on or off vs editing your repeating date field to remove a day.

- **Title:** Only shown to admins to quickly let you know what opening you're looking at.
- **Status:** Easy turn it on/off for booking, you can turn the whole calendar off in the Bookable Calendar
or here to turn off a single Opening.
- **Bookable Calendar:** Reference to the calendar this opening is for.
- **Booking Instance:** Not user created but links openings to instances.
- **Date:** The date field that is repeatable for when this Opening occurs.

### Bookable Calendar Opening Instance

This is an entity that you don't create directly, but per time slot that is open based on the
date field on Bookable Calendar Opening an instance is created. Then users will register
for indivdual instances. The indivdual instances are also what shows up on the
listing of all bookings you book.

- **Booking:** Points to all the Bookings that have Booked this Instance.
- **Booking Opening:** Points to the parent Opening.
- **Date:** The individual date that this Instance points to.

### Booking Contact

This is the entity that a front end user will be creating when they register.

- **Email:** The email for this booking, this is used emails to confirm the booking and a
link to edit the booking.
- **Party Size:** The amount of people this booking is for, this lets us know how many slots
are being taken by this booking.
- **Booking Instance:** The instance this booking points to.
- **Booking:** Not user created, the bookings this contact is linked to.

### Booking

This is another entity no one creates directly, but when a new Contact is created a new
Booking is created per person in the party size. This is how we know how many people
have booked each instance easily.

- **Created:** Time Booking was created.
- **Booking Instance:** The Booking Instance this Booking is for.
- **Booking Calendar:** The parent calendar this Booking is for. (this seems redundant but Instances might get cleaned up over time)
- **Contact:** The conctact that owns this Booking.
- **Booking Date:** The date this Booking is for. This is because I see a situation where
you need to clean up old Instances as you will most likely have thousands over the
course of a year, but you might still want historic records of Bookings and what timeslot they booked.

## API

There are some API endpoints exposed so you can create new Bookings however you'd like.

### Book an Instance

You can book an instance by hitting `/bookable-calendar/{instance_id}/book` then
pass in the body of `contact_info` with each machine name you want to save
with it's value. You can also follow the same method but pass in form-data, but
that doesn't need to be under a key of contact_info.

```
var myHeaders = new Headers();
myHeaders.append("Content-Type", "application/json");

var raw = JSON.stringify({
    "contact_info": {
        "email":"foo@bar.com",
        "party_size":2
    }
});

var requestOptions = {
  method: 'POST',
  headers: myHeaders,
  body: raw,
  redirect: 'follow'
};

fetch("/bookable-calendar/{$instance_id}/book", requestOptions)
  .then(response => response.text())
  .then(result => console.log(result))
  .catch(error => console.log('error', error));
```

## Todo

- Cleanup no longer matching instances maybe on cron.
- Allow for cleanup on Cron based on settings of old Instances, Bookings and Contacts
if you don't care about things older than X months.

## Quickly Delete Everything

```
$calendar_storage = \Drupal::entityTypeManager()->getStorage('bookable_calendar');
$calendars = $calendar_storage->loadMultiple();
foreach ($calendars as $calendar) {
  $calendar->delete();
}
$opening_storage = \Drupal::entityTypeManager()->getStorage('bookable_calendar_opening');
$openings = $opening_storage->loadMultiple();
foreach ($openings as $opening) {
  $opening->delete();
}
$instance_storage = \Drupal::entityTypeManager()->getStorage('bookable_calendar_opening_inst');
$instances = $instance_storage->loadMultiple();
foreach ($instances as $instance) {
  $instance->delete();
}
$booking_contact_storage = \Drupal::entityTypeManager()->getStorage('booking_contact');
$booking_contacts = $booking_contact_storage->loadMultiple();
foreach ($booking_contacts as $contact) {
  $contact->delete();
}
$booking_storage = \Drupal::entityTypeManager()->getStorage('booking');
$bookings = $booking_storage->loadMultiple();
foreach ($bookings as $booking) {
  $booking->delete();
}
```