/* eslint-env jest */
const EventReviewQuery = /* GraphQL */ `
  query EventReviewQuery($id: ID!) {
    node(id: $id) {
      ... on Event {
        title
        url
        review {
          status
          reviewer {
            _id
          }
          refusedReason
          comment
        }
      }
    }
  }
`;

describe('Event.review', () => {
  it('it fetches an event review', async () => {
    await Promise.all(
      [
        'event1',
        'eventCreateByAUserReviewAwaiting',
        'eventCreateByAUserReviewApproved',
        'eventCreateByAUserReviewRefused',
        'eventCreateByAUserReviewRefusedEn',
      ].map(async id => {
        await expect(
          graphql(
            EventReviewQuery,
            {
              id: global.toGlobalId('Event', id),
            },
            'internal',
          ),
        ).resolves.toMatchSnapshot(id);
      }),
    );
  });
});
