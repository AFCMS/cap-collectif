/* eslint-env jest */
const NodeQuery = /* GraphQL */ `
  query NodeQuery($id: ID!) {
    node(id: $id) {
      id
      __typename
    }
  }
`;

describe('Query.node', () => {
  it('returns null for bad IDs', async () => {
    await expect(graphql(NodeQuery, { id: 'abcde' })).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for users', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('User', 'user1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for replies', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Reply', 'reply1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for questionnaires', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Questionnaire', 'questionnaire1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for consultations', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Consultation', 'all') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for consultation steps', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('ConsultationStep', 'cstep1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for proposals', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Proposal', 'proposal1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for events', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Event', 'event1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for projects', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Project', 'project1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for simple questions', async () => {
    await expect(graphql(NodeQuery, { id: toGlobalId('Question', 1) })).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for multiple choice questions', async () => {
    await expect(graphql(NodeQuery, { id: toGlobalId('Question', 13) })).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for section questions', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Question', 301) }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for media questions', async () => {
    await expect(graphql(NodeQuery, { id: toGlobalId('Question', 11) })).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for comments', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Comment', 'eventComment1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for sources', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Source', 'source1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for opinions', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Opinion', 'opinion1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for versions', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Version', 'version1') }),
    ).resolves.toMatchSnapshot();
  });

  it('gets the correct ID and type name for arguments', async () => {
    await expect(
      graphql(NodeQuery, { id: toGlobalId('Argument', 'argument') }),
    ).resolves.toMatchSnapshot();
  });
});
