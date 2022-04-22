// @flow
import React, { PropTypes } from 'react';
import { FormattedMessage, injectIntl } from 'react-intl';
import { Col, Row, FormControl, Button } from 'react-bootstrap';
import { connect, type MapStateToProps } from 'react-redux';
import {
  fetchProjects,
  changeOrderBy,
  changeType,
  changeTheme,
  changeTerm,
} from '../../../redux/modules/project';
import Input from '../../Form/ReactBootstrapInput';
import type { State } from '../../../types';

const ProjectListFilter = React.createClass({
  propTypes: {
    projectTypes: PropTypes.array.isRequired,
    features: PropTypes.object.isRequired,
    themes: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired,
    orderBy: PropTypes.string.isRequired,
    intl: PropTypes.object.isRequired,
    type: PropTypes.string.isRequired,
  },

  getInitialState() {
    return {
      termInputValue: '',
    };
  },

  handleChangeTermInput(event) {
    this.setState({ termInputValue: event.target.value });
  },

  handleSubmit(e) {
    const { dispatch } = this.props;
    e.preventDefault();
    const value = this.state.termInputValue.length > 0 ? this.state.termInputValue : null;
    dispatch(changeTerm(value));
    dispatch(fetchProjects());
  },

  render() {
    const { projectTypes, features, themes, dispatch, orderBy, type, intl } = this.props;

    const filters = [];

    filters.push(
      <FormControl
        id="project-sorting"
        componentClass="select"
        type="select"
        name="orderBy"
        value={orderBy}
        onChange={e => {
          dispatch(changeOrderBy(e.target.value));
          dispatch(fetchProjects());
        }}>
        <option key="date" value="date">
          {intl.formatMessage({ id: 'global.filter_f_last' })}
        </option>
        <option key="popularity" value="popularity">
          {intl.formatMessage({ id: 'global.filter_f_popular' })}
        </option>
      </FormControl>,
    );

    if (projectTypes.length > 0) {
      filters.push(
        <FormControl
          id="project-type"
          componentClass="select"
          type="select"
          name="type"
          value={type}
          onChange={e => {
            dispatch(changeType(e.target.value));
            dispatch(fetchProjects());
          }}>
          <option key="all" value="">
            {intl.formatMessage({ id: 'global.select_project_types' })}
          </option>
          {projectTypes.map(projectType => (
            <FormattedMessage id={projectType.title} key={projectType.slug}>
              {message => <option value={projectType.slug}>{message}</option>}
            </FormattedMessage>
          ))}
        </FormControl>,
      );
    }

    if (features.themes) {
      filters.push(
        <FormControl
          id="project-theme"
          componentClass="select"
          type="select"
          name="theme"
          onChange={e => {
            dispatch(changeTheme(e.target.value));
            dispatch(fetchProjects());
          }}>
          <option key="all" value="">
            {intl.formatMessage({ id: 'global.select_themes' })}
          </option>
          {themes.map(theme => {
            return (
              <option key={theme.slug} value={theme.slug}>
                {theme.title}
              </option>
            );
          })}
        </FormControl>,
      );
    }

    filters.push(
      <form onSubmit={this.handleSubmit}>
        <Input
          id="project-search-input"
          type="text"
          placeholder="navbar.search"
          buttonAfter={
            <Button id="project-search-button" type="submit">
              <i className="cap cap-magnifier" />
            </Button>
          }
          groupClassName="project-search-group pull-right"
          value={this.state.value}
          onChange={this.handleChangeTermInput}
        />
      </form>,
    );

    const columnWidth = 12 / filters.length;

    return (
      <Row className="mb-35">
        {filters.map((filter, index) => {
          return (
            <Col key={index} xs={12} sm={columnWidth}>
              {filter}
            </Col>
          );
        })}
      </Row>
    );
  },
});

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => {
  return {
    features: state.default.features,
    themes: state.default.themes,
    orderBy: state.project.orderBy || 'date',
    type: state.project.type || 'all',
  };
};

export default connect(mapStateToProps)(injectIntl(ProjectListFilter));
