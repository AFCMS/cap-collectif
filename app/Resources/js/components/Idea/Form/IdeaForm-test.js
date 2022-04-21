/* eslint-env mocha */
/* eslint no-unused-expressions:0 */
import React from 'react';
import { expect } from 'chai';
import { shallow } from 'enzyme';
import IntlData from '../../../translations/FR';
import IdeaForm from '../Form/IdeaForm';

const props = {
  themes: [],
  onValidationFailure: () => {},
  onSubmitSuccess: () => {},
  onSubmitFailure: () => {},
};

const idea = {
  id: 1,
  theme: {
    id: 1,
  },
};

describe('<IdeaForm />', () => {
  it('it should render a form with all the fields but themes and confirm', () => {
    const wrapper = shallow(<IdeaForm {...props} isSubmitting={false} showThemes={false} {...IntlData} />);
    const form = wrapper.find('#idea-form');
    expect(form).to.have.length(1);
    expect(form.find('#idea_confirm')).to.have.length(0);
    expect(form.find('#idea_title')).to.have.length(1);
    expect(form.find('#idea_theme')).to.have.length(0);
    expect(form.find('#idea_body')).to.have.length(1);
    expect(form.find('#idea_object')).to.have.length(1);
    expect(form.find('#idea_url')).to.have.length(1);
    expect(form.find('#idea_media')).to.have.length(1);
  });

  it('it should render the confirm checkbox when idea is provided', () => {
    const wrapper = shallow(<IdeaForm {...props} idea={idea} isSubmitting={false} showThemes={false} {...IntlData} />);
    const form = wrapper.find('#idea-form');
    expect(form).to.have.length(1);
    expect(form.find('#idea_confirm')).to.have.length(1);
  });

  it('it should render theme field when feature is enabled', () => {
    const wrapper = shallow(<IdeaForm {...props} isSubmitting={false} showThemes={true} {...IntlData} />);
    const form = wrapper.find('#idea-form');
    expect(form).to.have.length(1);
    expect(form.find('#idea_theme')).to.have.length(1);
  });
});
