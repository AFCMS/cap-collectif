import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { Col, Row, Input, FormControl, Button } from 'react-bootstrap';
import { connect } from 'react-redux';
import {
  fetchProjects,
  changeOrderBy,
  changeType,
  changeTheme,
  changeTerm,
} from '../../../redux/modules/project';

const ProjectListFilter = React.createClass({
  propTypes: {
    projectTypes: PropTypes.array.isRequired,
    features: PropTypes.object.isRequired,
    themes: PropTypes.array,
    dispatch: PropTypes.func.isRequired,
    orderBy: PropTypes.string.isRequired,
    type: PropTypes.string.isRequired,
  },
  mixins: [IntlMixin],

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
    const { projectTypes, features, themes, dispatch, orderBy, type } = this.props;

    const filters = [];

    filters.push(
      <FormControl
        id="project-sorting"
        componentClass="select"
        type="select"
        name="orderBy"
        value={orderBy}
        onChange={(e) => {
          dispatch(changeOrderBy(e.target.value));
          dispatch(fetchProjects());
        }}
      >
        <option key="date" value="date">Les plus récents</option>
        <option key="popularity" value="popularity">Les plus populaires</option>
      </FormControl>
    );

    filters.push(
      <FormControl
        id="project-type"
        componentClass="select"
        type="select"
        name="type"
        value={type}
        onChange={(e) => {
          dispatch(changeType(e.target.value));
          dispatch(fetchProjects());
        }}
      >
        <option key="all" value="">Tous les types</option>
        {
          projectTypes.map((projectType) => {
            return <option value={projectType.slug}>{this.getIntlMessage(projectType.title)}</option>;
          })
        }
      </FormControl>
    );

    if (features.themes) {
      filters.push(
        <FormControl
          id="project-theme"
          componentClass="select"
          type="select"
          name="theme"
          onChange={(e) => {
            dispatch(changeTheme(e.target.value));
            dispatch(fetchProjects());
          }}
        >
          <option key="all" value="">Tous les thèmes</option>
          {
            themes.map((theme) => {
              return <option value={theme.slug}>{theme.title}</option>;
            })
          }
        </FormControl>
      );
    }

    filters.push(
      <form onSubmit={this.handleSubmit}>
        <Input
          id="project-search-input"
          type="text"
          placeholder={this.getIntlMessage('navbar.search')}
          buttonAfter={
            <Button id="project-search-button" type="submit">
              <i className="cap cap-magnifier"></i>
            </Button>
          }
          groupClassName="project-search-group pull-right"
          value={this.state.value}
          onChange={this.handleChangeTermInput}
        />
      </form>
    );

    const columnWidth = filters.length % 2 === 0 ? filters.length - 1 : filters.length;

    return (
      <Row>
        { filters.map((filter) => { return <Col xs={12} sm={columnWidth}>{filter}</Col>; }) }
      </Row>
    );
  },

});

const mapStateToProps = (state) => {
  return {
    features: state.default.features,
    themes: state.default.themes,
    orderBy: state.project.orderBy || 'date',
    type: state.project.type || 'all',
  };
};

export default connect(mapStateToProps)(ProjectListFilter);
