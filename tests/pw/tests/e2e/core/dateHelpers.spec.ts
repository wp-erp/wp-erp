import { test, expect } from '@utils/test';
import { toDate, dateOffset, upcomingMonday, daysAfter } from '@utils/helpers';

/**
 * Guards for the date helpers every leave spec anchors on.
 *
 * These need no browser and no site — they exist because a timezone bug in
 * `toDate()` silently shifted every generated date back by one day, but ONLY on
 * runs started between midnight and the UTC offset (06:00 here). It surfaced as
 * "Mon–Wed counts as three working days — expected 3, received 2" in two leave
 * specs, which reads exactly like a product defect in working-day counting.
 *
 * A time-of-day-dependent helper bug is the worst kind: it passes all day and
 * fails at night, so it gets written off as flake. These assertions fail
 * immediately instead, and they are cheap enough to run every time.
 */
test.describe('date helpers', () => {
    /** Day-of-week of a YYYY-MM-DD string, read in UTC so the parse is unambiguous. */
    const dayOfWeek = (isoDate: string): number => new Date(`${isoDate}T00:00:00Z`).getUTCDay();

    test('upcomingMonday always lands on a Monday', { tag: ['@tier1', '@harness'] }, async () => {
        // Every offset the suite actually uses, so a regression cannot hide in
        // the gaps between them.
        for (const weeksAhead of [1, 2, 3, 6, 7, 9, 11, 13, 15, 17]) {
            const monday = upcomingMonday(weeksAhead);

            expect(dayOfWeek(monday), `upcomingMonday(${weeksAhead}) = ${monday} is a Monday`).toBe(1);
        }
    });

    test('a Monday plus two days is still the same working week', { tag: ['@tier1', '@harness'] }, async () => {
        const monday = upcomingMonday(3);

        expect(dayOfWeek(daysAfter(monday, 1)), 'Monday + 1 is Tuesday').toBe(2);
        expect(dayOfWeek(daysAfter(monday, 2)), 'Monday + 2 is Wednesday').toBe(3);
        expect(dayOfWeek(daysAfter(monday, 4)), 'Monday + 4 is Friday').toBe(5);
    });

    test('toDate returns the local calendar day, not the UTC one', { tag: ['@tier1', '@harness'] }, async () => {
        const now = new Date();
        const expected = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

        expect(toDate(), 'today formats as the local date').toBe(expected);

        // The regression itself: a date built from local components must survive
        // the round trip unchanged, whatever the timezone offset is.
        const built = new Date();
        built.setDate(built.getDate() + 14);

        expect(dayOfWeek(toDate(built)), 'a local date keeps its weekday through toDate').toBe(built.getDay());
    });

    test('dateOffset moves whole days', { tag: ['@tier1', '@harness'] }, async () => {
        const start = new Date(`${toDate()}T00:00:00Z`);
        const twenty = new Date(`${dateOffset(20)}T00:00:00Z`);

        expect((twenty.getTime() - start.getTime()) / 86_400_000, 'dateOffset(20) is exactly 20 days out').toBe(20);
    });
});
