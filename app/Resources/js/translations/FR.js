
export default {
  locales: ['fr-FR'],
  messages: {
    alert: {
      danger: {
        add: {
          source: 'Désolé un problème est survenu lors de l\'ajout de la source.',
        },
        update: {
          source: 'Désolé un problème est survenu lors de l\'édition de votre source.',
        },
      },
      success: {
        add: {
          source: 'Merci ! La source a bien été ajoutée.',
        },
        delete: {
          source: 'Merci ! La source a bien été supprimée.',
        },
        report: {
          source: 'Merci ! La source a bien été signalée.',
        },
        update: {
          source: 'Merci ! Votre source a bien été modifiée.',
        },
      },
    },
    argument: {
      constraints: {
        max: 'Les avis sont limités à 2000 caractères. Soyez plus concis ou publiez une nouvelle proposition.',
        min: 'Le contenu doit faire au moins 3 caractères.',
      },
      filter: {
        no: 'Trier les arguments contre',
        simple: 'Trier les avis',
        yes: 'Trier les arguments pour',
      },
      no: {
        add: 'Ajouter un argument contre',
        list: '{num, plural, =0{0 argument contre} one{# argument contre} other{# arguments contre}}',
      },
      simple: {
        add: 'Déposer un avis',
        list: '{num, plural, =0{0 avis} one{# avis} other{# avis}}',
      },
      yes: {
        add: 'Ajouter un argument pour',
        list: '{num, plural, =0{0 argument pour} one{# argument pour} other{# arguments pour}}',
      },
    },
    comment: {
      constraints: {
        author_email: 'Cette valeur n\'est pas une adresse email valide.',
        author_name: 'Votre nom doit faire au moins 2 caractères',
        body: 'Votre commentaire doit faire au moins 2 caractères',
      },
      count_no_nb: '{count, plural, =0{commentaire} one{commentaire} other{commentaires}}',
      create_account_reason_1: 'valider que vous n\'êtes pas un robot une bonne fois pour toutes',
      create_account_reason_2: 'modifier/supprimer vos commentaires',
      create_account_reason_3: 'lier vos commentaires à votre profil',
      edited: 'modifié le',
      email_info: 'Pour valider que vous n\'êtes pas un robot.',
      list: '{num, plural, =0{0 commentaire} one{# commentaire} other{# commentaires}}',
      more: 'Voir plus de commentaires',
      public_name: 'Votre nom sera rendu public sur la plateforme.',
      publish: 'Publier un commentaire',
      report: {
        reported: 'Signalé',
        submit: 'Signaler',
      },
      submit: 'Commenter',
      submit_error: 'Désolé, un problème est survenu lors de l\'ajout de votre commentaire.',
      submit_success: 'Merci ! Votre commentaire a bien été ajouté.',
      trashed: {
        label: 'Dans la corbeille',
      },
      update: {
        button: 'Modifier',
      },
      vote: {
        remove: 'Annuler mon vote',
        submit: 'D\'accord',
      },
      why_create_account: 'Pourquoi créer un compte ?',
      with_my_account: 'Commenter avec mon compte',
      without_account: 'Commenter sans créer de compte',
      write: 'Ecrire un commentaire...',
    },
    editor: {
      align: {
        center: 'Centrer',
        justify: 'Justifier',
        left: 'Gauche',
        right: 'Droite',
        title: 'Aligner',
      },
      bullet: 'Liste',
      image: 'Image',
      link: 'Lien',
      list: 'Liste ordonnée',
      size: {
        large: 'Grand',
        normal: 'Normal',
        small: 'Petit',
      },
      url: 'Visiter l\'URL',
    },
    global: {
      advanced_filters: 'Filtres avancés',
      all_required: 'Tous les champs sont obligatoires.',
      anonymous: 'Anonyme',
      answer: 'Répondre',
      arguments: '{num, plural, =0{0 argument} one{# argument} other{# arguments}}',
      back: 'Retour',
      cancel: 'Annuler',
      change: 'Changer',
      close: 'Fermer',
      comment: 'Commentaire',
      comments: '{num, plural, =0{0 commentaire} one{# commentaire} other{# commentaires}}',
      constraints: {
        notBlank: 'Cette valeur ne doit pas être vide.',
      },
      content: 'Contenu',
      dates: {
        between: 'Du {start} au {end}',
        full_day: 'Le {date} à {time}',
        part_day: 'Le {date} de {startTime} à {endTime}',
      },
      delete: 'Supprimer',
      done: 'Terminé',
      edit: 'Modifier',
      edited: 'modifié le',
      edited_on: 'Modifié le {updated}',
      filter_f_comments: 'Les plus commentées',
      filter_f_last: 'Les plus récentes',
      filter_f_old: 'Les plus anciennes',
      filter_f_random: 'Tri aléatoire',
      filter_f_votes: 'Les plus votées',
      filter_favorable: 'Favorables',
      filter_comments: 'Commentés',
      filter_last: 'Récents',
      filter_old: 'Anciens',
      filter_popular: 'Populaires',
      filter_votes: 'Votés',
      fullname: 'Nom complet *',
      hidden_email: 'Adresse électronique (cachée) *',
      insert: 'Insérer',
      link: 'Lien *',
      links: '{num, plural, =0{0 proposition liée} one{# proposition liée} other{# propositions liées}}',
      loading: 'Chargement...',
      login: 'Connexion',
      modal: {
        report: {
          form: {
            body: 'Message *',
            status: 'Quelle est la nature du problème ? *',
          },
          infos: 'L\'équipe du site évalue les contenus et les utilisateurs signalés régulièrement pour déterminer s\'ils portent atteinte au règlement de la communauté. Les comptes sont sanctionnés pour toute infraction au règlement de la communauté et peuvent être clôturés en cas de violations graves ou répétées.',
          title: 'Signaler un contenu',
        },
      },
      more: 'Voir plus',
      name: 'Nom ',
      or: 'OU',
      preview: 'Aperçu',
      publish: 'Publier',
      read_more: 'Afficher la suite',
      register: 'S\'inscrire',
      remove: 'Supprimer',
      report: {
        reported: 'Signalé',
        submit: 'Signaler',
      },
      select: 'Choisir une valeur',
      select_district: 'Quartier',
      select_status: 'Statut',
      select_theme: 'Thème',
      select_type: 'Type de contributeur',
      share: 'Partager',
      simple_arguments: '{num, plural, =0{0 avis} one{# avis} other{# avis}}',
      sources: '{num, plural, =0{0 source} one{# source} other{# sources}}',
      title: 'Titre',
      versions: '{num, plural, =0{0 modification} one{# modification} other{# modifications}}',
      votes: '{num, plural, =0{0 vote} one{# vote} other{# votes}}',
      votes_evolution: 'évolution des votes',
    },
    opinion: {
      add_new_source: 'Proposer une source',
      add_new_version: 'Proposer une modification',
      add_new_version_infos: 'Merci d\'examiner les modifications existantes en premier lieu afin de ne pas soumettre de doublon. Vous pouvez voter pour celles existantes !',
      appendices: {
        hide: 'Masquer {title}.',
        show: 'Afficher {title}',
      },
      body: 'Proposition',
      body_help: 'Rédigez votre proposition',
      constraints: {
        body: 'Le contenu de la proposition doit faire au moins 2 caractères.',
        title: 'Le titre de la proposition doit faire au moins 2 caractères.',
      },
      diff: {
        infos: 'Les ajouts en vert et les suppressions en rouge',
        title: 'Modification(s) proposée(s)',
        tooltip: 'Voir les modifications',
      },
      header: {
        article: 'Article',
        opinion: 'Proposition',
        version: 'Modification',
      },
      link: {
        add_new: 'Ajouter une proposition liée',
        constraints: {
          type: 'Veuillez choisir un type pour soumettre une proposition liée.',
        },
        help: {
          body: 'Rédigez votre proposition',
          title: 'Quel est l\'objet de votre proposition ?',
          type: 'Quel est le type de votre proposition ?',
        },
        info: 'Votre proposition sera liée à :',
        infos: 'Merci d\'examiner les propositions existantes en premier lieu afin de ne pas soumettre de doublon. Vous pouvez voter pour celles existantes !',
        type: 'Type de proposition*',
      },
      no_new_link: 'Aucune proposition liée',
      no_new_source: 'Aucune source proposée',
      no_new_version: 'Aucune modification proposée',
      progress: {
        done: '{num, plural, =0{0 vote favorable} one{# vote favorable} other{# votes favorables}}.',
        left: '{left, plural, =0{0 nécessaire} one{# nécessaire} other{# nécessaires}} pour atteindre {max}.',
        reached: 'Cette proposition a atteint le seuil avec {with, plural, =0{0 vote} one{# vote} other{# votes}}.',
      },
      ranking: {
        articles: 'Top {max} des articles',
        opinions: 'Top {max} des propositions',
        versions: 'Top {max} des modifications',
      },
      request: {
        create_vote: {
          success: 'Merci ! Votre vote a bien été pris en compte.',
        },
        delete_vote: {
          success: 'Votre vote a bien été supprimé.',
        },
        failure: 'Une erreur est survenue, veuillez réessayer.',
      },
      title: 'Titre*',
      title_help: 'Quel est l\'objet de votre proposition ?',
      type: 'Type de proposition*',
      type_help: 'Quel est le type de votre proposition ?',
      version: {
        body: 'Modification *',
        body_error: 'Vous devez modifier le contenu de la proposition d\'origine pour pouvoir proposer une modification.',
        body_helper: 'Modifiez le texte',
        comment: 'Explication',
        comment_helper: 'Expliquez pourquoi vous souhaitez apporter ces modifications',
        confirm: 'En modifiant ma contribution, je comprends que tous les votes qui lui sont associés seront réinitialisés.',
        confirm_error: 'Vous devez confirmer la perte de vos votes pour continuer.',
        filter: 'Trier les modifications',
        title: 'Titre *',
        title_error: 'Le titre doit contenir au moins 2 caractères.',
      },
      version_comment: 'Explication',
      version_parent: 'Modification de : ',
    },
    project: {
      votes: {
        delete: 'Retirer',
        nb: '{num, plural, =0{0 proposition sélectionnée} one{# proposition sélectionnée} other{# propositions sélectionnées}}',
        no_active_step: 'Aucun étape de vote n\'est actuellement active.',
        title: 'Détails de mes votes',
        type: {
          budget: '(vote selon le budget)',
          simple: '(vote simple)',
        },
        widget: {
          budget: 'Budget',
          count: 'Mes votes',
          no_value: 'Non renseigné',
          left: 'Restant',
          spent: 'Dépensé',
          step: 'Étape',
        },
      },
    },
    proposal: {
      add: 'Faire une proposition',
      body: 'Description',
      constraints: {
        body: 'La description de la proposition doit faire au moins 2 caractères.',
        district: 'Sélectionnez un quartier',
        question_mandatory: 'Ce champ est obligatoire.',
        theme: 'Sélectionnez un thème',
        title: 'Le titre de la proposition doit faire au moins 2 caractères.',
      },
      count: '{num, plural, =0{0 proposition} one{# proposition} other{# propositions}}',
      delete: {
        confirm: 'Voulez-vous vraiment supprimer la proposition "{title}" ?',
      },
      description: 'Description',
      details: 'Détails',
      district: 'Quartier',
      empty: 'Aucune proposition',
      infos: {
        header: '{user} {theme, select, no {le {createdDate}} other {dans {themeLink}, le {createdDate}}}',
      },
      no_status: 'Aucun statut',
      random_search: 'Afficher d\'autres propositions',
      request: {
        create: {
          failure: 'Toutes nos excuses ! Une erreur est survenue, merci de réessayer plus tard.',
          success: 'Merci ! Votre proposition a bien été créée.',
        },
        delete: {
          failure: 'Toutes nos excuses ! Une erreur est survenue, merci de réessayer plus tard.',
          success: 'Votre proposition a bien été supprimée.',
        },
        delete_vote: {
          failure: 'Toutes nos excuses ! Une erreur est survenue, merci de réessayer plus tard.',
          success: 'Merci, votre vote a bien été supprimé.',
        },
        update: {
          failure: 'Toutes nos excuses ! Une erreur est survenue, merci de réessayer plus tard.',
          success: 'Votre proposition a bien été modifiée.',
        },
        vote: {
          failure: 'Toutes nos excuses ! Une erreur est survenue, merci de réessayer plus tard.',
          success: 'Merci, votre vote a bien été pris en compte.',
        },
      },
      search: 'Rechercher une proposition',
      select: {
        district: 'Sélectionnez un quartier',
        theme: 'Sélectionnez un thème',
      },
      theme: 'Theme',
      title: 'Titre',
      trashed: {
        label: 'Proposition dans la corbeille.',
        motive: 'Motif : {motive}',
      },
      vote: {
        add: 'Voter pour',
        constraints: {
          email: 'Veuillez entrer une adresse email valide',
          username: 'Le nom doit faire au moins 2 caractères.',
        },
        count: '{num, plural, =0{0 vote} one{# vote} other{# votes}}',
        count_no_nb: '{count, plural, =0{vote} one{vote} other{votes}}',
        delete: 'Annuler mon vote',
        form: {
          comment: 'Commentaire',
          comment_placeholder: 'Pourquoi soutenez-vous ce projet ? (optionnel)',
          email: 'Adresse électronique',
          private: 'Voter de manière anonyme',
          username: 'Nom',
        },
        modal: {
          title: 'Voter',
        },
        not_enough_credits: 'Pas assez de crédits. Désélectionnez un projet ou sélectionnez un projet moins coûteux.',
        show_more: 'Voir tous les votes',
        vote_with_my_account: 'Voter avec mon compte',
      },
    },
    reporting: {
      status: {
        error: 'Information erronée',
        off_topic: 'Propos hors-sujet',
        offending: 'Contenu raciste, offensant ou haineux',
        sexual: 'Contenu à caractère sexuel',
        spam: 'Spam ou contenu trompeur',
      },
    },
    share: {
      facebook: 'Facebook',
      googleplus: 'Google+',
      link: 'Lien de partage',
      mail: 'Email',
      twitter: 'Twitter',
    },
    source: {
      add: 'Créer une source',
      add_infos: 'Merci d\'examiner les sources existantes en premier lieu afin de ne pas soumettre de doublon. Vous pouvez voter pour celles existantes !',
      body: 'Description *',
      check: 'En modifiant ma source, je comprends que les votes qui lui sont associés seront réinitialisés.',
      constraints: {
        body: 'Le contenu de la source doit faire au moins 2 caractères.',
        category: 'Veuillez choisir un type pour soumettre une source',
        check: 'Veuillez cocher cette case pour continuer.',
        link: 'Cette valeur n\'est pas une URL valide.',
        title: 'Le titre de la source doit faire au moins 2 caractères.',
      },
      delete_modal: {
        bold: 'Êtes-vous sûr de vouloir supprimer cette source ?',
        title: 'Supprimer une source',
        infos: 'Cette suppression marquera la fin de ce contenu à jamais.',
      },
      link: 'Lien *',
      title: 'Titre *',
      type: 'Type *',
      update: 'Modifier une source',
    },
    step: {
      status: {
        closed: 'Terminé',
        future: 'À venir',
        open: 'En cours',
      },
    },
    vote: {
      aria_label: {
        mitige: 'Souhaitez-vous déclarer être mitigé sur cette proposition ?',
        nok: 'Souhaitez-vous déclarer ne pas être d\'accord avec cette proposition ?',
        ok: 'Souhaitez-vous déclarer être d\'accord avec cette proposition ?',
      },
      aria_label_active: {
        mitige: 'Vous avez déclaré être mitigé sur cette proposition',
        nok: 'Vous avez déclaré n\'être pas d\'accord avec cette proposition',
        ok: 'Vous avez déclaré être d\'accord avec cette proposition',
      },
      cancel: 'Annuler mon vote',
      count_no_nb: '{count, plural, =0{vote} one{vote} other{votes}}',
      date: 'Date',
      form: 'Formulaire de vote',
      mitige: 'Mitigé',
      nok: 'Pas d\'accord',
      ok: 'D\'accord',
      popover: {
        body: 'Vous devez être connecté pour réaliser cette action.',
        login: 'Connexion',
        signin: 'Inscription',
        title: 'Connectez-vous pour contribuer',
      },
    },
  },
};
